<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel "partners" = data petani kopi mitra untuk modul Portal Kemitraan.
     * "on_time_rate" dan "quality_score" adalah metrik performa yang
     * ditampilkan di tab "Petani Mitra" dan di Laporan tab "Supplier".
     */
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('commodity')->default('Biji Kopi'); // jenis komoditas yang dipasok
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('on_time_rate', 5, 2)->default(0); // persentase (%) ketepatan waktu kirim
            $table->decimal('quality_score', 5, 2)->default(0); // skor kualitas barang, skala 0-100
            $table->date('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
