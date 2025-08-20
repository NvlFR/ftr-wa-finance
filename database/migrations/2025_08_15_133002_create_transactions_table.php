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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_phone'); // Nomor WA pengirim
            $table->enum('type', ['pemasukan', 'pengeluaran']); // Untuk MVP, kita hanya handle 2 tipe ini
            $table->decimal('amount', 15, 2); // Jumlah uang
            $table->string('description'); // Keterangan transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
