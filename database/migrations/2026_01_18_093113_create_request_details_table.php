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
        Schema::create('detail_permintaans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('bagian_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('barang_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('permintaan_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('jumlah');
            $table->enum('approved', ['pending', 'approved', 'rejected', 'approved_sebagian'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_permintaans');
    }
};
