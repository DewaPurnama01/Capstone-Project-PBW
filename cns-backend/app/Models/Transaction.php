<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "transactions" (nota belanja / transaksi POS).
 */
class Transaction extends Model
{
    protected $fillable = [
        'code', 'customer_id', 'cashier_id', 'payment_method', 'status', 'total', 'transacted_at',
    ];

    protected function casts(): array
    {
        return ['transacted_at' => 'datetime'];
    }

    // belongsTo = kebalikan dari hasMany: satu transaksi milik satu pelanggan
    public function customer() { return $this->belongsTo(Customer::class); }

    // satu transaksi dicatat oleh satu kasir (user dengan role kasir/owner)
    public function cashier() { return $this->belongsTo(User::class, 'cashier_id'); }

    // satu transaksi terdiri dari banyak baris item (menu yang dibeli)
    public function items() { return $this->hasMany(TransactionItem::class); }
}
