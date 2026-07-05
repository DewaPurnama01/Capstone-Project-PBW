<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
