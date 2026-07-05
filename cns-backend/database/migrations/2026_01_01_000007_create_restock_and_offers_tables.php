<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // REQ-2026-001
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->text('specification')->nullable(); // spesifikasi kopi yang diminta
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
            $table->unsignedInteger('eta_days');
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
