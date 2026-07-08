<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/** Mengisi data pelanggan contoh untuk modul Manajemen Pelanggan. */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, telepon, email, segmen, poin, menu favorit, jumlah kunjungan, total belanja, tanggal gabung]
        $customers = [
            ['Andi Wijaya', '0812-3456-7890', 'andi@email.com', 'VIP', 2850, 'Kopi Susu', 48, 2340000, '2025-01-15'],
            ['Siti Rahma', '0813-5678-9012', 'siti@email.com', 'Member', 1240, 'Matcha Latte', 24, 980000, '2025-03-02'],
            ['Budi Santoso', '0814-2345-6789', 'budi@email.com', 'Reguler', 580, 'Americano', 12, 456000, '2025-05-10'],
            ['Diana Putri', '0815-6789-0123', 'diana@email.com', 'VIP', 3420, 'Cappuccino', 62, 3150000, '2024-11-20'],
            ['Rizky Aditya', '0816-8901-2345', 'rizky@email.com', 'Member', 1680, 'Cold Brew', 31, 1280000, '2025-02-18'],
            ['Fitriani Dewi', '0817-0123-4567', 'fitri@email.com', 'Baru', 120, 'Kopi Susu', 3, 95000, '2026-05-28'],
            ['Hendra Gunawan', '0818-2345-6789', 'hendra@email.com', 'Reguler', 760, 'Americano', 17, 620000, '2025-05-30'],
            ['Maya Sari', '0819-4567-8901', 'maya@email.com', 'Member', 1950, 'Matcha Latte', 38, 1560000, '2025-04-12'],
        ];

        foreach ($customers as [$name, $phone, $email, $segment, $points, $fav, $visits, $spent, $joined]) {
            Customer::create([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'segment' => $segment,
                'loyalty_points' => $points,
                'favorite_menu' => $fav,
                'visit_count' => $visits,
                'total_spent' => $spent,
                'joined_at' => $joined,
                'last_visit_at' => now()->subDays(rand(0, 5)), // kunjungan terakhir: 0-5 hari yang lalu
            ]);
        }
    }
}
