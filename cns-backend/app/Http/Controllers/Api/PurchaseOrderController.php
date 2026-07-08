<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\QualityControl;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Modul Purchase Orders (laporan 4.7): melacak PO yang sudah terbit dari
 * Portal Kemitraan, sampai pembayaran lunas & barang diterima.
 */
class PurchaseOrderController extends Controller
{
    /** READ — daftar PO, bisa difilter berdasarkan status pengiriman */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['partner', 'inventoryItem', 'payments']);

        if ($request->filled('status') && $request->status !== 'Semua') {
            $statusMap = [
                'Dikirim' => 'dikirim', 'QC Lulus' => 'qc_lulus',
                'Selesai' => 'selesai', 'Retur' => 'retur',
            ];
            $query->where('delivery_status', $statusMap[$request->status] ?? $request->status);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'data' => $orders,
            'summary' => [
                'total' => PurchaseOrder::count(),
                'dikirim' => PurchaseOrder::where('delivery_status', 'dikirim')->count(),
                'belum_bayar_amount' => (float) PurchaseOrder::where('payment_status', '!=', 'lunas')->get()
                    ->sum(fn ($po) => $po->remaining_amount), // remaining_amount = accessor di model
                'selesai_bulan_ini' => PurchaseOrder::where('delivery_status', 'selesai')
                    ->whereMonth('updated_at', now()->month)->count(),
            ],
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json(['data' => $purchaseOrder->load('partner', 'inventoryItem', 'payments', 'qualityControl')]);
    }

    /** Mencatat satu pembayaran (PO boleh dicicil beberapa kali) */
    public function recordPayment(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:Tunai,Transfer,QRIS',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data pembayaran tidak valid.'], 422);
        }

        PurchaseOrderPayment::create([
            'purchase_order_id' => $purchaseOrder->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'paid_at' => now(),
        ]);

        // Cek total semua pembayaran dibanding total tagihan, untuk menentukan
        // status pembayaran berikutnya: sudah lunas, atau baru sebagian.
        $totalDibayar = $purchaseOrder->payments()->sum('amount');
        $purchaseOrder->update([
            'payment_status' => $totalDibayar >= $purchaseOrder->total ? 'lunas' : 'sebagian',
        ]);

        return response()->json(['message' => 'Pembayaran dicatat.', 'data' => $purchaseOrder->fresh('payments')]);
    }

    /**
     * Konfirmasi penerimaan barang + input skor Quality Control.
     * Skor >= 70 dianggap lulus: stok inventori otomatis bertambah dan
     * PO ditandai selesai. Skor di bawah itu -> barang diretur.
     */
    public function confirmReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'quality_score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data QC tidak valid.'], 422);
        }

        $lulus = $request->quality_score >= 70;

        QualityControl::create([
            'purchase_order_id' => $purchaseOrder->id,
            'quality_score' => $request->quality_score,
            'passed' => $lulus,
            'notes' => $request->notes,
            'checked_at' => now(),
        ]);

        if ($lulus) {
            $this->selesaikanPurchaseOrder($purchaseOrder);
        } else {
            $purchaseOrder->update(['delivery_status' => 'retur', 'received_at' => now()]);
        }

        // Skor kualitas petani di halaman "Petani Mitra" disamakan dengan
        // skor QC terakhir ini (versi sederhana, cukup untuk demo laporan).
        $purchaseOrder->partner->update(['quality_score' => $request->quality_score]);

        return response()->json(['message' => 'Penerimaan & QC dicatat.', 'data' => $purchaseOrder->fresh(['qualityControl', 'partner'])]);
    }

    // Menandai PO selesai: tambah stok inventori + tandai request asalnya selesai juga
    private function selesaikanPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->update(['delivery_status' => 'selesai', 'received_at' => now()]);
        $purchaseOrder->inventoryItem->increment('current_stock', $purchaseOrder->qty);

        if ($purchaseOrder->reference_code) {
            RestockRequest::where('code', $purchaseOrder->reference_code)->update(['status' => 'selesai']);
        }
    }
}
