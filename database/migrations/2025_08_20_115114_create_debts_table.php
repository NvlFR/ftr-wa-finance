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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('cascade');
            $table->string('user_phone')->nullable();
            $table->enum('type', ['hutang', 'piutang']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->enum('status', ['belum lunas', 'lunas'])->default('belum lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
