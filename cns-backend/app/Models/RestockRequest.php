<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockRequest extends Model
{
    protected $fillable = [
        'code', 'inventory_item_id', 'specification', 'qty_needed', 'unit',
        'status', 'created_by', 'broadcasted_at',
    ];

    protected function casts(): array
    {
        return ['broadcasted_at' => 'datetime'];
    }

    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function offers() { return $this->hasMany(PartnerOffer::class); }
}
