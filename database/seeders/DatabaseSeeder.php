<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Sistem dimulai dari kosong.
     * Seluruh data dibuat melalui registrasi & penggunaan sistem.
     *
     * Langkah setup:
     * 1. php artisan migrate:fresh
     * 2. php artisan serve
     * 3. Buka /register → buat akun Owner
     * 4. Login → tambah inventori, mitra, dst.
     */
    public function run(): void
    {
        // Tidak ada seed data — sistem dimulai dari nol.
        // Semua data dibuat oleh pengguna melalui antarmuka sistem.
    }
}
