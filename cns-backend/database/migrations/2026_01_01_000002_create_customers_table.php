<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel "customers" = data pelanggan untuk modul Manajemen Pelanggan.
     * Kolom "segment", "loyalty_points", dan "total_spent" mendukung fitur
     * program loyalitas yang disebut di laporan (bagian 4.3).
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            // enum = kolom yang nilainya dibatasi hanya boleh salah satu dari daftar berikut
            $table->enum('segment', ['Baru', 'Reguler', 'Member', 'VIP'])->default('Baru');
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->string('favorite_menu')->nullable();
            $table->unsignedInteger('visit_count')->default(0); // jumlah kunjungan/transaksi
            $table->decimal('total_spent', 14, 2)->default(0); // total belanja, dipakai untuk ALV di Laporan
            $table->date('joined_at')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();

            // index mempercepat pencarian/filter berdasarkan kolom segment
            $table->index('segment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
