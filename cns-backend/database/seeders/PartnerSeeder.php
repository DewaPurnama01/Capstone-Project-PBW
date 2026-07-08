<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/** Mengisi data petani kopi mitra untuk modul Portal Kemitraan. */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, telepon, alamat, komoditas, % ketepatan waktu, skor kualitas, tanggal gabung]
        $partners = [
            ['Pak Iwan Kusuma', '0821-1111-2222', 'Wonosobo, Jawa Tengah', 'Biji Kopi Arabica', 96.5, 92, '2024-06-01'],
            ['Bu Sulastri', '0821-3333-4444', 'Kintamani, Bali', 'Biji Kopi Robusta', 88, 85, '2024-08-15'],
            ['Pak Made Suarta', '0821-5555-6666', 'Bandung, Jawa Barat', 'Biji Kopi Arabica', 91, 89, '2025-01-10'],
            ['Bu Ratna Sari', '0821-7777-8888', 'Toraja, Sulawesi Selatan', 'Biji Kopi Arabica', 94, 95, '2024-03-20'],
        ];

        foreach ($partners as [$name, $phone, $address, $commodity, $onTime, $quality, $joined]) {
            Partner::create([
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'commodity' => $commodity,
                'is_active' => true,
                'on_time_rate' => $onTime,
                'quality_score' => $quality,
                'joined_at' => $joined,
            ]);
        }
    }
}
