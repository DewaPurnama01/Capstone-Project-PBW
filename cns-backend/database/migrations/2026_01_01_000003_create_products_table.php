<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel "products" = daftar menu kafe (kopi, makanan, snack) yang
     * dipakai saat membuat transaksi di modul Transaksi & POS.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Minuman', 'Makanan', 'Snack'])->default('Minuman');
            $table->decimal('price', 12, 2); // harga jual ke pelanggan
            $table->decimal('cost_price', 12, 2)->default(0); // harga modal, dipakai menghitung margin di Laporan
            $table->boolean('is_active')->default(true); // menu bisa disembunyikan tanpa dihapus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
