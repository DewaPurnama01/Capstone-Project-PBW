<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dua tabel untuk alur Portal Kemitraan (laporan bagian 4.6):
     *
     * 1. "restock_requests" = permintaan pengadaan biji kopi yang dibuat
     *    Owner/Admin, lalu disiarkan (broadcast) ke petani mitra.
     * 2. "partner_offers"   = tawaran harga & estimasi kirim dari tiap
     *    petani untuk satu permintaan tertentu.
     *
     * Alurnya: draft -> disiarkan -> ditawar (ada offer masuk) -> po_dibuat
     * (setelah salah satu offer dipilih) -> selesai.
     */
    public function up(): void
    {
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // contoh: REQ-2026-001
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->text('specification')->nullable(); // spesifikasi kopi yang diminta (grade, roast, dll)
            $table->decimal('qty_needed', 12, 2);
            $table->string('unit', 20)->default('kg');
            $table->enum('status', ['draft', 'disiarkan', 'ditawar', 'po_dibuat', 'selesai'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('broadcasted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_request_id')->constrained('restock_requests')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->decimal('price_per_unit', 14, 2);
            $table->unsignedInteger('eta_days'); // estimasi jumlah hari sampai barang tiba
            // "menunggu" = belum diputuskan, "dipilih" = jadi PO, "ditolak" = kalah dari offer lain
            $table->enum('status', ['menunggu', 'dipilih', 'ditolak'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_offers');
        Schema::dropIfExists('restock_requests');
    }
};
