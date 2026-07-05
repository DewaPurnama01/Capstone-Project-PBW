<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public function payments() { return $this->hasMany(PurchaseOrderPayment::class); }
    public function qualityControl() { return $this->hasOne(QualityControl::class); }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - $this->paid_amount);
    }
}
