<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['Biji Kopi', 'Bahan Baku', 'kg', 2.5, 5, 30, 150000, 'Portal Kemitraan', true],
            ['Susu Full Cream', 'Bahan Baku', 'liter', 3, 10, 40, 18000, 'Supplier Susu Segar', false],
            ['Sirup Matcha', 'Bahan Baku', 'liter', 1.2, 3, 15, 85000, 'Toko Bahan Kopi', false],
            ['Gula Aren', 'Bahan Baku', 'kg', 8, 5, 20, 25000, 'Toko Bahan Kopi', false],
            ['Cup Plastik 16oz', 'Kemasan', 'pcs', 400, 200, 1000, 700, 'Distributor Kemasan Jaya', false],
            ['Sedotan', 'Kemasan', 'pcs', 600, 300, 1500, 150, 'Distributor Kemasan Jaya', false],
            ['Roti Tawar', 'Makanan', 'pcs', 15, 10, 50, 12000, 'Bakery Mitra', false],
            ['Croissant Beku', 'Makanan', 'pcs', 20, 15, 60, 9000, 'Bakery Mitra', false],
            ['Kentang Beku', 'Makanan', 'kg', 6, 5, 25, 35000, 'Distributor Frozen Food', false],
        ];

        foreach ($items as [$name, $category, $unit, $stock, $min, $max, $price, $supplier, $isCoffee]) {
            InventoryItem::create([
                'name' => $name,
                'category' => $category,
                'unit' => $unit,
                'current_stock' => $stock,
                'min_stock' => $min,
                'max_stock' => $max,
                'unit_price' => $price,
                'supplier_name' => $supplier,
                'is_coffee_bean' => $isCoffee,
            ]);
        }
    }
}
