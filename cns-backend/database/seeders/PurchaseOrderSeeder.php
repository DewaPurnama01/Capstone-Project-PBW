<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Partner;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\QualityControl;
use Illuminate\Database\Seeder;

/**
 * Mengisi 2 contoh Purchase Order: satu yang masih "dikirim" (belum dibayar,
 * belum diterima) dan satu yang sudah "selesai" (lunas + lulus QC), supaya
 * modul Purchase Orders langsung punya contoh data di kedua kondisi tsb.
 */
class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $coffee = InventoryItem::where('is_coffee_bean', true)->first();
        $iwan = Partner::where('name', 'Pak Iwan Kusuma')->first();
        $sulastri = Partner::where('name', 'Bu Sulastri')->first();

        $po1 = PurchaseOrder::create([
            'code' => 'PO-2026-045',
            'reference_code' => 'REQ-2026-003',
            'partner_id' => $iwan->id,
            'inventory_item_id' => $coffee->id,
            'qty' => 10,
            'unit' => 'kg',
            'unit_price' => 160000,
            'total' => 1600000,
            'delivery_status' => 'dikirim',
            'payment_status' => 'belum_bayar',
            'estimated_delivery' => now()->addDays(3),
        ]);

        $po2 = PurchaseOrder::create([
            'code' => 'PO-2026-038',
            'reference_code' => 'REQ-2026-002',
            'partner_id' => $sulastri->id,
            'inventory_item_id' => $coffee->id,
            'qty' => 15,
            'unit' => 'kg',
            'unit_price' => 90000,
            'total' => 1350000,
            'delivery_status' => 'selesai',
            'payment_status' => 'lunas',
            'estimated_delivery' => now()->subDays(4),
            'received_at' => now()->subDays(3),
        ]);

        PurchaseOrderPayment::create([
            'purchase_order_id' => $po2->id,
            'amount' => 1350000,
            'method' => 'Transfer',
            'paid_at' => now()->subDays(3),
        ]);

        QualityControl::create([
            'purchase_order_id' => $po2->id,
            'quality_score' => 91,
            'passed' => true,
            'notes' => 'Kualitas biji kopi baik, sesuai spesifikasi.',
            'checked_at' => now()->subDays(3),
        ]);
    }
}
