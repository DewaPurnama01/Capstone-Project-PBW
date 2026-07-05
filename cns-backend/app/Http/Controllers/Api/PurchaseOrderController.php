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

class PurchaseOrderController extends Controller
{
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
                    ->sum(fn ($po) => $po->remaining_amount),
                'selesai_bulan_ini' => PurchaseOrder::where('delivery_status', 'selesai')
                    ->whereMonth('updated_at', now()->month)->count(),
            ],
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json(['data' => $purchaseOrder->load('partner', 'inventoryItem', 'payments', 'qualityControl')]);
    }

    /**
     * Mencatat pembayaran pada PO yang belum lunas.
     */
    public function recordPayment(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:Tunai,Transfer,QRIS',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data pembayaran tidak valid.'], 422);
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            PurchaseOrderPayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'amount' => $request->amount,
                'method' => $request->method,
                'paid_at' => now(),
            ]);

            $paid = $purchaseOrder->payments()->sum('amount');
            $purchaseOrder->update([
                'payment_status' => $paid >= $purchaseOrder->total ? 'lunas' : 'sebagian',
            ]);
        });

        return response()->json(['message' => 'Pembayaran dicatat.', 'data' => $purchaseOrder->fresh('payments')]);
    }

    /**
     * Konfirmasi penerimaan barang + input hasil Quality Control.
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

        $passed = $request->quality_score >= 70;

        DB::transaction(function () use ($request, $purchaseOrder, $passed) {
            QualityControl::create([
                'purchase_order_id' => $purchaseOrder->id,
                'quality_score' => $request->quality_score,
                'passed' => $passed,
                'notes' => $request->notes,
                'checked_at' => now(),
            ]);

            $purchaseOrder->update([
                'delivery_status' => $passed ? 'qc_lulus' : 'retur',
                'received_at' => now(),
            ]);

            if ($passed) {
                $purchaseOrder->inventoryItem->increment('current_stock', $purchaseOrder->qty);
                $purchaseOrder->update(['delivery_status' => 'selesai']);

                if ($purchaseOrder->reference_code) {
                    RestockRequest::where('code', $purchaseOrder->reference_code)
                        ->update(['status' => 'selesai']);
                }
            }

            // Update metrik performa petani (rata-rata bergerak sederhana)
            $partner = $purchaseOrder->partner;
            $onTime = $purchaseOrder->received_at->lte($purchaseOrder->estimated_delivery?->endOfDay() ?? now());
            $partner->update([
                'quality_score' => round((($partner->quality_score ?? 0) + $request->quality_score) / 2, 2),
                'on_time_rate' => round((($partner->on_time_rate ?? 0) + ($onTime ? 100 : 0)) / 2, 2),
            ]);
        });

        return response()->json(['message' => 'Penerimaan & QC dicatat.', 'data' => $purchaseOrder->fresh(['qualityControl', 'partner'])]);
    }
}
