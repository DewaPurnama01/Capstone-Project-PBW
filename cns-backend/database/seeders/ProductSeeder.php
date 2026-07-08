<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/** Mengisi daftar menu kafe yang dipakai di modul Transaksi & POS. */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Array 2 dimensi: tiap baris = [nama, kategori, harga jual, harga modal]
        $products = [
            ['Kopi Susu', 'Minuman', 30000, 12000],
            ['Americano', 'Minuman', 25000, 9000],
            ['Cappuccino', 'Minuman', 32000, 13000],
            ['Cold Brew', 'Minuman', 28000, 11000],
            ['Matcha Latte', 'Minuman', 35000, 15000],
            ['Croissant', 'Makanan', 18000, 7000],
            ['Roti Bakar', 'Makanan', 22000, 8000],
            ['French Fries', 'Snack', 20000, 8000],
        ];

        // list($a, $b, $c, $d) = ... membongkar satu baris array jadi 4 variabel sekaligus
        foreach ($products as [$name, $category, $price, $cost]) {
            Product::create([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'cost_price' => $cost,
                'is_active' => true,
            ]);
        }
    }
}
