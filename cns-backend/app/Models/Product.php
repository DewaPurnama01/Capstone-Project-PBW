<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'category', 'price', 'cost_price', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
