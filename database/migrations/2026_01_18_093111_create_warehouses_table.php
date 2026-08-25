<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gudangs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barang_id')
                ->constrained('barangs')
                ->cascadeOnDelete();

            $table->foreignId('bagian_id')
                ->constrained('bagians')
                ->cascadeOnDelete();

            $table->integer('stok')->default(0);
            $table->timestamps();

            // 🔒 KUNCI ANTI DOUBLE
            $table->unique(['barang_id', 'bagian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gudangs');
    }
};
