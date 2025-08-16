<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('investment_transactions', function (Blueprint $table) {
        $table->id();
        $table->string('user_phone');
        $table->enum('type', ['beli', 'jual']);
        $table->string('asset_name'); // Contoh: "Bitcoin", "Saham BBCA", "Emas Antam"
        $table->string('asset_type'); // Contoh: "crypto", "saham", "emas", "reksadana"
        $table->decimal('quantity', 18, 8); // Jumlah unit, misal 0.01 BTC atau 100 lembar saham
        $table->decimal('price_per_unit', 19, 4); // Harga per unit saat transaksi
        $table->decimal('total_amount', 19, 4); // Total uang yang dikeluarkan/diterima
        $table->timestamp('transaction_date')->useCurrent();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_transactions');
    }
};
