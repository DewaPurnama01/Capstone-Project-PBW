<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function getStockStatusAttribute(): string
    {
        if ($this->min_stock <= 0) return 'aman';
        $ratio = $this->current_stock / $this->min_stock;
        if ($ratio < 0.6) return 'kritis';
        if ($this->current_stock <= $this->min_stock) return 'rendah';
        return 'aman';
    }

    public function getStockPercentAttribute(): float
    {
        if ($this->max_stock <= 0) return 0;
        return round(min(100, ($this->current_stock / $this->max_stock) * 100), 1);
    }
}
