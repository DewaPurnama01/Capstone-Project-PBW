<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['Bahan Baku', 'Kemasan', 'Makanan'])->default('Bahan Baku');
            $table->string('unit', 20); // kg, liter, pcs, dll
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('max_stock', 12, 2)->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->string('supplier_name')->nullable();
            $table->boolean('is_coffee_bean')->default(false); // flag khusus alur Portal Kemitraan
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
