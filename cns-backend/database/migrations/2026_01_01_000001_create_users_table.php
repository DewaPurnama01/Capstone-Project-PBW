<?php

// MIGRATION = "cetakan" struktur tabel database, ditulis dengan kode PHP
// (bukan SQL manual). Setiap kali file ini dijalankan (php artisan migrate),
// Laravel akan membuat tabel di database sesuai definisi di bawah.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel "users" menyimpan akun untuk login (Owner, Admin, Kasir).
     * Kolom "role" inilah yang dipakai untuk RBAC (Role-Based Access Control):
     * setiap request ke API akan dicek role user-nya sebelum diizinkan.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // kolom "id", auto increment, primary key
            $table->string('name'); // nama lengkap untuk ditampilkan di UI
            $table->string('username')->unique(); // dipakai untuk login, tidak boleh sama
            $table->string('email')->unique()->nullable();
            $table->string('password'); // disimpan dalam bentuk hash (terenkripsi), bukan teks asli
            $table->enum('role', ['owner', 'admin', 'kasir'])->default('kasir'); // batasi nilai yang boleh diisi
            $table->string('avatar_initial', 2)->nullable(); // inisial 2 huruf untuk avatar bulat di sidebar
            $table->boolean('is_active')->default(true); // akun bisa dinonaktifkan tanpa dihapus
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // otomatis menambah kolom created_at & updated_at
        });
    }

    // Kebalikan dari up(): dipanggil saat migration di-rollback (php artisan migrate:rollback)
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
