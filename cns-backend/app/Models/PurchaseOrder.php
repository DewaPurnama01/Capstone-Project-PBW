<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "purchase_orders" (PO ke petani mitra).
 */
class PurchaseOrder extends Model
{
    protected $fillable = [
        'code', 'reference_code', 'partner_id', 'partner_offer_id', 'inventory_item_id',
        'qty', 'unit', 'unit_price', 'total', 'delivery_status', 'payment_status',
        'estimated_delivery', 'received_at',
    ];

    protected function casts(): array
    {
        return ['estimated_delivery' => 'date', 'received_at' => 'datetime'];
    }

    public function partner() { return $this->belongsTo(Partner::class); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function offer() { return $this->belongsTo(PartnerOffer::class, 'partner_offer_id'); }

    // satu PO bisa dibayar bertahap/dicicil -> banyak baris pembayaran
    public function payments() { return $this->hasMany(PurchaseOrderPayment::class); }

    // hasOne = relasi one-to-one: satu PO hanya punya satu hasil QC
    public function qualityControl() { return $this->hasOne(QualityControl::class); }

    // Accessor: total yang sudah dibayar (dijumlah dari semua baris payments)
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    // Accessor: sisa tagihan yang belum dibayar
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - $this->paid_amount);
    }
}
