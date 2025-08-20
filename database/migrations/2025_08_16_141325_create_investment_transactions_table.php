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
        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_phone');
            $table->enum('type', ['beli', 'jual']);
            $table->string('asset_name');
            $table->string('asset_type');
            $table->decimal('quantity', 18, 8);
            $table->decimal('price_per_unit', 19, 4);
            $table->decimal('total_amount', 19, 4);
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
