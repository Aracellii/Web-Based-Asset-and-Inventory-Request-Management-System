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
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom enum role lama
            $table->dropColumn('role');
            
            // Tambahkan foreign key role_id ke tabel roles
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dan kolom role_id
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            
            // Kembalikan kolom enum role
            $table->enum('role', ['super_admin', 'keuangan', 'admin', 'user'])->default('user')->after('password');
        });
    }
};
