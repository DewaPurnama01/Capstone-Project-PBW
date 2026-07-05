<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
