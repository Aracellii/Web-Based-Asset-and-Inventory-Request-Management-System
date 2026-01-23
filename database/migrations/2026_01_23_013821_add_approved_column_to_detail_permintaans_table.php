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
        Schema::table('detail_permintaans', function (Blueprint $table) {
            $table->enum('approved', ['pending', 'approved', 'rejected'])->default('pending')->after('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_permintaans', function (Blueprint $table) {
            $table->dropColumn('approved');
        });
    }
};
