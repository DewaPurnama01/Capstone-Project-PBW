<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * SEEDER = kode untuk mengisi database dengan data contoh (dummy),
 * supaya aplikasi bisa langsung dicoba tanpa harus input manual dulu.
 * Dijalankan lewat: php artisan db:seed (atau migrate --seed).
 *
 * Urutan pemanggilan di bawah ini penting: data yang menjadi "acuan"
 * (users, products, dst) harus dibuat dulu sebelum data yang bergantung
 * padanya (transaksi butuh pelanggan & produk yang sudah ada).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            InventoryItemSeeder::class,
            PartnerSeeder::class,
            TransactionSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}
