<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Partner;
use App\Models\PartnerOffer;
use App\Models\PurchaseOrder;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Modul Portal Kemitraan Petani (laporan 4.6). Alur pengadaan biji kopi:
 *
 *   1. createRequest()  -> Owner/Admin membuat draf permintaan
 *   2. broadcast()       -> permintaan disiarkan ke petani mitra
 *   3. submitOffer()     -> tiap petani yang menawar dicatat di sini
 *   4. selectOffer()     -> tawaran terbaik dipilih, PO otomatis dibuat
 *
 * Setelah PO dibuat, proses selanjutnya (pengiriman & Quality Control)
 * ditangani oleh PurchaseOrderController.
 */
class PartnershipController extends Controller
{
    /** Ringkasan halaman Portal Kemitraan: stok kopi, daftar request, daftar petani */
    public function index()
    {
        $coffeeBean = InventoryItem::where('is_coffee_bean', true)->first();

        $activeRequests = RestockRequest::with(['inventoryItem', 'offers.partner'])
            ->whereIn('status', ['draft', 'disiarkan', 'ditawar', 'po_dibuat'])
            ->latest()->get();

        $history = RestockRequest::with(['inventoryItem', 'offers.partner'])
            ->where('status', 'selesai')
            ->latest()->limit(20)->get();

        $partners = Partner::orderByDesc('quality_score')->get();

        // Ambil PO yang terkait dengan request di atas, supaya frontend bisa
        // tahu sudah sampai tahap mana (dikirim/selesai/dsb) untuk tiap request.
        $kodeRequestTerkait = $activeRequests->pluck('code')->merge($history->pluck('code'));
        $purchaseOrders = PurchaseOrder::whereIn('reference_code', $kodeRequestTerkait)
            ->get(['id', 'code', 'reference_code', 'delivery_status', 'payment_status']);

        return response()->json([
            'coffee_stock' => $coffeeBean,
            'coffee_critical' => $coffeeBean ? $coffeeBean->stock_status === 'kritis' : false,
            'active_requests' => $activeRequests,
            'history' => $history,
            'purchase_orders' => $purchaseOrders,
            'partners' => $partners,
        ]);
    }

    public function partners()
    {
        return response()->json(['data' => Partner::orderByDesc('quality_score')->get()]);
    }

    /** Langkah 1: buat draf permintaan restock biji kopi */
    public function createRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'specification' => 'nullable|string',
            'qty_needed' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data permintaan tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $urutan = RestockRequest::whereYear('created_at', now()->year)->count() + 1;
        $code = 'REQ-' . now()->format('Y') . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);

        $restockRequest = RestockRequest::create([
            'code' => $code,
            'inventory_item_id' => $request->inventory_item_id,
            'specification' => $request->specification,
            'qty_needed' => $request->qty_needed,
            'unit' => $request->unit ?? 'kg',
            'status' => 'draft',
            'created_by' => JWTAuth::user()?->id,
        ]);

        return response()->json(['message' => 'Draf permintaan dibuat.', 'data' => $restockRequest], 201);
    }

    /** Langkah 2: siarkan draf ke seluruh petani aktif (tombol manual di frontend) */
    public function broadcast(RestockRequest $restockRequest)
    {
        $restockRequest->update(['status' => 'disiarkan', 'broadcasted_at' => now()]);

        return response()->json([
            'message' => 'Permintaan berhasil disiarkan ke petani mitra aktif.',
            'data' => $restockRequest->fresh(),
        ]);
    }

    /**
     * Langkah 3: catat tawaran dari seorang petani. Di aplikasi nyata ini bisa
     * datang dari portal/WhatsApp petani; di sini Owner/Admin yang mencatat
     * secara manual lewat form "Input Penawaran Petani".
     */
    public function submitOffer(Request $request, RestockRequest $restockRequest)
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:partners,id',
            'price_per_unit' => 'required|numeric|min:0',
            'eta_days' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Penawaran tidak valid.'], 422);
        }

        $offer = PartnerOffer::create([
            'restock_request_id' => $restockRequest->id,
            'partner_id' => $request->partner_id,
            'price_per_unit' => $request->price_per_unit,
            'eta_days' => $request->eta_days,
            'status' => 'menunggu',
        ]);

        $restockRequest->update(['status' => 'ditawar']);

        return response()->json(['message' => 'Penawaran diterima.', 'data' => $offer], 201);
    }

    /** Langkah 4: pilih satu tawaran terbaik -> otomatis membuat Purchase Order */
    public function selectOffer(Request $request, PartnerOffer $offer)
    {
        $offer->update(['status' => 'dipilih']);

        // Tawaran lain untuk request yang sama otomatis ditandai "ditolak"
        PartnerOffer::where('restock_request_id', $offer->restock_request_id)
            ->where('id', '!=', $offer->id)
            ->update(['status' => 'ditolak']);

        $restockRequest = $offer->restockRequest;
        $purchaseOrder = $this->buatPurchaseOrderDariOffer($restockRequest, $offer);

        $restockRequest->update(['status' => 'po_dibuat']);

        return response()->json(['message' => 'Purchase Order otomatis dibuat.', 'data' => $purchaseOrder], 201);
    }

    // Helper kecil supaya method selectOffer() di atas tetap ringkas dan mudah dibaca
    private function buatPurchaseOrderDariOffer(RestockRequest $restockRequest, PartnerOffer $offer): PurchaseOrder
    {
        $urutan = PurchaseOrder::whereYear('created_at', now()->year)->count() + 1;
        $code = 'PO-' . now()->format('Y') . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);

        return PurchaseOrder::create([
            'code' => $code,
            'reference_code' => $restockRequest->code,
            'partner_id' => $offer->partner_id,
            'partner_offer_id' => $offer->id,
            'inventory_item_id' => $restockRequest->inventory_item_id,
            'qty' => $restockRequest->qty_needed,
            'unit' => $restockRequest->unit,
            'unit_price' => $offer->price_per_unit,
            'total' => $offer->price_per_unit * $restockRequest->qty_needed,
            'delivery_status' => 'dikirim',
            'payment_status' => 'belum_bayar',
            'estimated_delivery' => now()->addDays($offer->eta_days),
        ]);
    }
}
