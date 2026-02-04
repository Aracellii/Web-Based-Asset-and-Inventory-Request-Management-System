<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BagianSeeder::class, // Setup bagian terlebih dahulu
            SimplePermissionSeeder::class, // Setup roles & permissions (Simple)
            // ShieldSeeder::class, // DEPRECATED: Ganti pakai SimplePermissionSeeder
            UserSeeder::class,
            BarangSeeder::class,
            GudangSeeder::class,
        ]);
    }
}
