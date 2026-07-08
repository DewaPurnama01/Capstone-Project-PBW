<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "transaction_items" (rincian menu per transaksi).
 */
class TransactionItem extends Model
{
    protected $fillable = ['transaction_id', 'product_id', 'product_name', 'qty', 'price', 'subtotal'];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
