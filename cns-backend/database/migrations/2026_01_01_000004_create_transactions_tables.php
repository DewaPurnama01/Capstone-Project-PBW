<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dua tabel untuk modul Transaksi & POS:
     * - "transactions"       = data induk (1 nota belanja)
     * - "transaction_items"  = rincian menu yang dibeli di 1 transaksi
     *
     * Ini contoh relasi "one-to-many" (satu transaksi punya banyak item),
     * dihubungkan lewat foreign key "transaction_id".
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // kode nota, contoh: TRX-2026-001
            // foreignId()->constrained() = foreign key ke tabel lain (relasi antar tabel)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_method', ['QRIS', 'Tunai', 'Transfer'])->default('QRIS');
            $table->enum('status', ['proses', 'selesai', 'dibatalkan'])->default('proses');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamp('transacted_at')->useCurrent(); // waktu transaksi terjadi
            $table->timestamps();

            $table->index(['status', 'transacted_at']);
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // nama produk disalin (snapshot) saat transaksi dibuat,
                                              // supaya laporan lama tidak berubah walau nama produk diedit
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 14, 2); // = price * qty, dihitung di controller saat menyimpan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Urutan drop dibalik: tabel "anak" (transaction_items) dihapus dulu
        // sebelum tabel "induk" (transactions), karena ada foreign key.
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
