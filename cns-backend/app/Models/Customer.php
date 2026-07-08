<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "customers" (Manajemen Pelanggan).
 */
class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'segment', 'loyalty_points',
        'favorite_menu', 'visit_count', 'total_spent', 'joined_at', 'last_visit_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'last_visit_at' => 'datetime',
            'total_spent' => 'decimal:2',
        ];
    }

    /**
     * RELASI one-to-many: satu pelanggan bisa punya banyak transaksi.
     * Setelah ini, kita bisa menulis $customer->transactions untuk
     * mengambil semua transaksi milik pelanggan tsb tanpa menulis query manual.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
