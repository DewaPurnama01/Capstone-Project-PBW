<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel "inventory_items" untuk modul Manajemen Inventori.
     * "min_stock" dan "max_stock" dipakai untuk menghitung status stok
     * (Kritis/Rendah/Aman) yang tampil sebagai progress bar di halaman Inventori.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Bahan Baku', 'Kemasan', 'Makanan'])->default('Bahan Baku');
            $table->string('unit', 20); // satuan: kg, liter, pcs, dst
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0); // batas bawah -> kalau stok di bawah ini, statusnya "kritis"
            $table->decimal('max_stock', 12, 2)->default(0); // batas atas -> dipakai untuk hitung persen progress bar
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->string('supplier_name')->nullable();
            // penanda khusus: kalau true, restock item ini WAJIB lewat Portal Kemitraan
            // (bukan restock manual biasa) -- sesuai laporan bagian 4.5 & 4.6
            $table->boolean('is_coffee_bean')->default(false);
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
