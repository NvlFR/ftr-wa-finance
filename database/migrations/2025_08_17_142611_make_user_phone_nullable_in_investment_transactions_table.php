<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_transactions', function (Blueprint $table) {
            // Mengubah kolom user_phone agar bisa bernilai NULL (kosong)
            $table->string('user_phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('investment_transactions', function (Blueprint $table) {
            // Mengembalikan aturan seperti semula
            $table->string('user_phone')->nullable(false)->change();
        });
    }
};
