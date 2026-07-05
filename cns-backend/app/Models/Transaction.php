<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'code', 'customer_id', 'cashier_id', 'payment_method', 'status', 'total', 'transacted_at',
    ];

    protected function casts(): array
    {
        return ['transacted_at' => 'datetime'];
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function cashier() { return $this->belongsTo(User::class, 'cashier_id'); }
    public function items() { return $this->hasMany(TransactionItem::class); }
}
