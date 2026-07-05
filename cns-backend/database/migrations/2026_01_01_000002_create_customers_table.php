<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('segment', ['Baru', 'Reguler', 'Member', 'VIP'])->default('Baru');
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->string('favorite_menu')->nullable();
            $table->unsignedInteger('visit_count')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->date('joined_at')->nullable();
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();

            $table->index('segment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
