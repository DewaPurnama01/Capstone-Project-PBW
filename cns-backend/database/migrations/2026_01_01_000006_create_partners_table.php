<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('commodity')->default('Biji Kopi');
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('on_time_rate', 5, 2)->default(0); // % ketepatan waktu
            $table->decimal('quality_score', 5, 2)->default(0); // skor kualitas 0-100
            $table->date('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
