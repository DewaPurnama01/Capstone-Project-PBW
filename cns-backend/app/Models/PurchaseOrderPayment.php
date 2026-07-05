<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderPayment extends Model
{
    protected $fillable = ['purchase_order_id', 'amount', 'method', 'paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
}
