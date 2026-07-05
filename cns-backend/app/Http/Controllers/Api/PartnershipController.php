<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Partner;
use App\Models\PartnerOffer;
use App\Models\PurchaseOrder;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PartnershipController extends Controller
{
    /**
     * Ringkasan Portal Kemitraan: status stok kopi kritis, daftar request aktif,
     * dan daftar petani mitra beserta metrik performa.
     */
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

        $relatedCodes = $activeRequests->pluck('code')->merge($history->pluck('code'));
        $purchaseOrders = PurchaseOrder::whereIn('reference_code', $relatedCodes)
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

    /**
     * Owner/Admin membuat draf permintaan lalu menyiarkan (broadcast) ke seluruh petani aktif.
     */
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

        $count = RestockRequest::whereYear('created_at', now()->year)->count() + 1;
        $code = 'REQ-' . now()->format('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $req = RestockRequest::create([
            'code' => $code,
            'inventory_item_id' => $request->inventory_item_id,
            'specification' => $request->specification,
            'qty_needed' => $request->qty_needed,
            'unit' => $request->unit ?? 'kg',
            'status' => 'draft',
            'created_by' => JWTAuth::user()?->id,
        ]);

        return response()->json(['message' => 'Draf permintaan dibuat.', 'data' => $req], 201);
    }

    /**
     * Menyiarkan (broadcast) permintaan ke seluruh petani aktif.
     */
    public function broadcast(RestockRequest $restockRequest)
    {
        $restockRequest->update(['status' => 'disiarkan', 'broadcasted_at' => now()]);

        $activePartnersCount = Partner::where('is_active', true)->count();

        return response()->json([
            'message' => "Permintaan berhasil disiarkan ke {$activePartnersCount} petani mitra aktif.",
            'data' => $restockRequest->fresh(),
        ]);
    }

    /**
     * Petani memberi penawaran (disimulasikan lewat dashboard internal untuk keperluan demo).
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

    /**
     * Memilih penawaran terbaik -> otomatis membuat Purchase Order.
     */
    public function selectOffer(Request $request, PartnerOffer $offer)
    {
        $result = DB::transaction(function () use ($offer) {
            $offer->update(['status' => 'dipilih']);
            $restockRequest = $offer->restockRequest;

            PartnerOffer::where('restock_request_id', $restockRequest->id)
                ->where('id', '!=', $offer->id)
                ->update(['status' => 'ditolak']);

            $count = PurchaseOrder::whereYear('created_at', now()->year)->count() + 1;
            $code = 'PO-' . now()->format('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
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

            $restockRequest->update(['status' => 'po_dibuat']);

            return $po;
        });

        return response()->json(['message' => 'Purchase Order otomatis dibuat.', 'data' => $result], 201);
    }
}
