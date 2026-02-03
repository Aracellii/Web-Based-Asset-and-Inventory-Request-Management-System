<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BagianSeeder::class, // Setup bagian terlebih dahulu
            ShieldSeeder::class, // Setup roles & permissions
            UserSeeder::class,
            BarangSeeder::class,
            GudangSeeder::class,
        ]);
    }
}
