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
    Schema::table('savings', function (Blueprint $table) {
        // Menambahkan kolom baru setelah 'status'
        $table->boolean('is_emergency_fund')->default(false)->after('status');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('savings', function (Blueprint $table) {
        $table->dropColumn('is_emergency_fund');
    });
    }
};
