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
        Schema::create('laporans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('bagian_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('barang_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('detail_verif_id')->nullable()->constrained('detail_terverifikasis')->onDelete('set null');
            $table->year('tahun_anggaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
