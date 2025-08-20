<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('user_phone');
            $table->string('category'); // Kategori budget, misal: "makanan", "transportasi"
            $table->decimal('amount', 15, 2); // Jumlah budget yang ditetapkan
            $table->unsignedInteger('month'); // Bulan (1-12)
            $table->unsignedInteger('year'); // Tahun (contoh: 2025)
            $table->timestamps();

            // Mencegah duplikasi budget untuk kategori yang sama di bulan yang sama
            $table->unique(['user_phone', 'category', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
