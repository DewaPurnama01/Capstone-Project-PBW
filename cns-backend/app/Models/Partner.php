<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "partners" (petani kopi mitra, Portal Kemitraan).
 */
class Partner extends Model
{
    protected $fillable = [
        'name', 'phone', 'address', 'commodity', 'photo', 'is_active',
        'on_time_rate', 'quality_score', 'joined_at',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'joined_at' => 'date'];
    }

    public function offers() { return $this->hasMany(PartnerOffer::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
