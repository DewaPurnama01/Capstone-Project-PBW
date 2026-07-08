<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tiga tabel untuk modul Purchase Orders (laporan bagian 4.7):
     *
     * 1. "purchase_orders"         = PO yang terbit setelah offer petani dipilih
     * 2. "purchase_order_payments" = riwayat pembayaran (PO bisa dicicil)
     * 3. "quality_controls"        = hasil pengecekan kualitas saat barang diterima
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // contoh: PO-2026-045
            $table->string('reference_code')->nullable(); // kode REQ- asal permintaan
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('partner_offer_id')->nullable()->constrained('partner_offers')->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('qty', 12, 2);
            $table->string('unit', 20)->default('kg');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total', 14, 2);
            // status pengiriman barang
            $table->enum('delivery_status', ['dikirim', 'diterima', 'qc_lulus', 'retur', 'selesai'])->default('dikirim');
            // status pembayaran ke petani (terpisah dari status pengiriman)
            $table->enum('payment_status', ['belum_bayar', 'sebagian', 'lunas'])->default('belum_bayar');
            $table->date('estimated_delivery')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['Tunai', 'Transfer', 'QRIS'])->default('Transfer');
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->decimal('quality_score', 5, 2); // skala 0-100
            $table->boolean('passed')->default(true); // lulus QC atau tidak (>=70 dianggap lulus)
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
        Schema::dropIfExists('purchase_order_payments');
        Schema::dropIfExists('purchase_orders');
    }
};
