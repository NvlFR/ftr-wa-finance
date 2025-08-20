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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->string('user_phone');
            $table->string('goal_name'); // Nama tujuan, misal: "Laptop Baru"
            $table->decimal('target_amount', 15, 2); // Target uang yang ingin dicapai
            $table->decimal('current_amount', 15, 2)->default(0); // Uang yang sudah terkumpul
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
