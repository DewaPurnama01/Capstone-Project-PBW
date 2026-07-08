<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "quality_controls" (hasil pengecekan kualitas barang
 * yang diterima dari petani mitra).
 */
class QualityControl extends Model
{
    protected $fillable = ['purchase_order_id', 'quality_score', 'passed', 'notes', 'photo', 'checked_at'];

    protected function casts(): array
    {
        return ['passed' => 'boolean', 'checked_at' => 'datetime'];
    }

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
}
