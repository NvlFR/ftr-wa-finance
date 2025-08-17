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
    Schema::table('debts', function (Blueprint $table) {
        $table->foreignId('party_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        $table->dropColumn('person_name');
        $table->string('user_phone')->nullable()->change(); // Pastikan kolom ini sudah nullable
    });
}

public function down(): void
{
    Schema::table('debts', function (Blueprint $table) {
        $table->dropForeign(['party_id']);
        $table->dropColumn('party_id');
        $table->string('person_name')->after('user_phone');
    });
}
};
