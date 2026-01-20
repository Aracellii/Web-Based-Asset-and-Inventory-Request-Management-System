<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BagianSeeder::class,
            UserSeeder::class,
            BarangSeeder::class,
            GudangSeeder::class,
        ]);
    }
}