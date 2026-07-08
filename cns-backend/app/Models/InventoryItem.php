<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk tabel "inventory_items" (Manajemen Inventori).
 * Berisi dua "accessor" (getXxxAttribute) yaitu properti turunan yang
 * dihitung otomatis dari kolom lain, bukan disimpan langsung di database.
 */
class InventoryItem extends Model
{
    protected $fillable = [
        'name', 'category', 'unit', 'current_stock', 'min_stock', 'max_stock',
        'unit_price', 'supplier_name', 'is_coffee_bean',
    ];

    protected function casts(): array
    {
        return ['is_coffee_bean' => 'boolean'];
    }

    public function restockRequests() { return $this->hasMany(RestockRequest::class); }

    /**
     * Accessor: $item->stock_status akan otomatis memanggil fungsi ini.
     * Logikanya sederhana: bandingkan stok saat ini dengan batas minimum.
     *   - di bawah 60% dari minimum  -> "kritis"
     *   - di bawah/sama dengan minimum -> "rendah"
     *   - selebihnya -> "aman"
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->min_stock <= 0) {
            return 'aman';
        }

        $ratio = $this->current_stock / $this->min_stock;

        if ($ratio < 0.6) {
            return 'kritis';
        }

        if ($this->current_stock <= $this->min_stock) {
            return 'rendah';
        }

        return 'aman';
    }

    /**
     * Accessor: $item->stock_percent -> persentase stok saat ini
     * dibanding stok maksimum, dipakai untuk lebar progress bar di frontend.
     */
    public function getStockPercentAttribute(): float
    {
        if ($this->max_stock <= 0) {
            return 0;
        }

        $percent = ($this->current_stock / $this->max_stock) * 100;

        return round(min(100, $percent), 1); // dibatasi maksimal 100%
    }
}
