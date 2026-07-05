<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // TRX-2026-001
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_method', ['QRIS', 'Tunai', 'Transfer'])->default('QRIS');
            $table->enum('status', ['proses', 'selesai', 'dibatalkan'])->default('proses');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamp('transacted_at')->useCurrent();
            $table->timestamps();

            $table->index(['status', 'transacted_at']);
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // snapshot nama produk saat transaksi
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
