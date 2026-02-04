<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Urutan penting:
     * 1. BagianSeeder - Karena User & Gudang butuh bagian_id
     * 2. SimplePermissionSeeder - Karena User butuh roles
     * 3. UserSeeder - Assign roles ke users
     * 4. BarangSeeder - Master data barang
     * 5. GudangSeeder - Stok barang per bagian (butuh barang_id dan bagian_id)
     * 6. PermintaanSeeder - Sample permintaan (butuh user_id, barang_id, bagian_id)
     * 7. LogAktivitasSeeder - Sample log (butuh user_id, gudang_id, barang_id)
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai Database Seeding...');
        $this->command->newLine();
        
        $this->call([
            BagianSeeder::class,              // 1. Setup 6 bagian
            SimplePermissionSeeder::class,    // 2. Setup roles & permissions  
            UserSeeder::class,                // 3. Buat 13 users
            BarangSeeder::class,              // 4. Buat 8 barang
            GudangSeeder::class,              // 5. Buat 48 stok gudang
            PermintaanSeeder::class,          // 6. Buat sample permintaan (5 permintaan)
            LogAktivitasSeeder::class,        // 7. Buat sample log aktivitas (7 logs)
        ]);
        
        $this->command->newLine();
        $this->command->info('✅ Database seeding selesai!');
        $this->command->info('📝 Login: admin@gmail.com / 12345678');
        $this->command->newLine();
    }
}
