<?php

namespace Database\Seeders;

use App\Models\Permintaan;
use App\Models\DetailPermintaan;
use App\Models\DetailTerverifikasi;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermintaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Membuat sample permintaan dari berbagai user dengan status berbeda:
     * - Pending (menunggu approval)
     * - Approved (sudah disetujui)
     * - Rejected (ditolak)
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding Permintaan & Detail...');

        // Get users per role
        $userStaff = User::where('role', 'user')->get();
        
        if ($userStaff->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada user staff, skip seeding permintaan');
            return;
        }

        $permintaanData = [];

        // 1. Permintaan APPROVED dari User TU (Pensil 10, Buku 5)
        $user1 = $userStaff->where('bagian_id', 1)->first();
        if ($user1) {
            $p1 = Permintaan::create([
                'user_id' => $user1->id,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(9),
            ]);

            $detail1 = DetailPermintaan::create([
                'permintaan_id' => $p1->id,
                'bagian_id' => 1,
                'barang_id' => 1, // Pensil
                'jumlah' => 10,
                'approved' => 'approved',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(9),
            ]);

            DetailTerverifikasi::create([
                'detail_permintaan_id' => $detail1->id,
                'bagian_id' => 1,
                'barang_id' => 1,
                'jumlah' => 10,
                'approved' => 'approved',
                'created_at' => now()->subDays(9),
            ]);

            $detail2 = DetailPermintaan::create([
                'permintaan_id' => $p1->id,
                'bagian_id' => 1,
                'barang_id' => 2, // Buku
                'jumlah' => 5,
                'approved' => 'approved',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(9),
            ]);

            DetailTerverifikasi::create([
                'detail_permintaan_id' => $detail2->id,
                'bagian_id' => 1,
                'barang_id' => 2,
                'jumlah' => 5,
                'approved' => 'approved',
                'created_at' => now()->subDays(9),
            ]);

            $permintaanData[] = "✓ Permintaan #{$p1->id} (APPROVED) - {$user1->name}";
        }

        // 2. Permintaan PENDING dari User SP (Kertas A4 20)
        $user2 = $userStaff->where('bagian_id', 2)->first();
        if ($user2) {
            $p2 = Permintaan::create([
                'user_id' => $user2->id,
                'created_at' => now()->subDays(3),
            ]);

            DetailPermintaan::create([
                'permintaan_id' => $p2->id,
                'bagian_id' => 2,
                'barang_id' => 3, // Kertas A4
                'jumlah' => 20,
                'approved' => 'pending',
                'created_at' => now()->subDays(3),
            ]);

            $permintaanData[] = "⏳ Permintaan #{$p2->id} (PENDING) - {$user2->name}";
        }

        // 3. Permintaan REJECTED dari User PHP (Galon 15)
        $user3 = $userStaff->where('bagian_id', 3)->first();
        if ($user3) {
            $p3 = Permintaan::create([
                'user_id' => $user3->id,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(6),
            ]);

            DetailPermintaan::create([
                'permintaan_id' => $p3->id,
                'bagian_id' => 3,
                'barang_id' => 4, // Galon
                'jumlah' => 15,
                'approved' => 'rejected',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(6),
            ]);

            $permintaanData[] = "✗ Permintaan #{$p3->id} (REJECTED) - {$user3->name}";
        }

        // 4. Permintaan APPROVED dari User PP (Dispenser 2, Binder 10)
        $user4 = $userStaff->where('bagian_id', 4)->first();
        if ($user4) {
            $p4 = Permintaan::create([
                'user_id' => $user4->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ]);

            $detail4a = DetailPermintaan::create([
                'permintaan_id' => $p4->id,
                'bagian_id' => 4,
                'barang_id' => 5, // Dispenser
                'jumlah' => 2,
                'approved' => 'approved',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ]);

            DetailTerverifikasi::create([
                'detail_permintaan_id' => $detail4a->id,
                'bagian_id' => 4,
                'barang_id' => 5,
                'jumlah' => 2,
                'approved' => 'approved',
                'created_at' => now()->subDays(4),
            ]);

            $detail4b = DetailPermintaan::create([
                'permintaan_id' => $p4->id,
                'bagian_id' => 4,
                'barang_id' => 6, // Binder
                'jumlah' => 10,
                'approved' => 'approved',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ]);

            DetailTerverifikasi::create([
                'detail_permintaan_id' => $detail4b->id,
                'bagian_id' => 4,
                'barang_id' => 6,
                'jumlah' => 10,
                'approved' => 'approved',
                'created_at' => now()->subDays(4),
            ]);

            $permintaanData[] = "✓ Permintaan #{$p4->id} (APPROVED) - {$user4->name}";
        }

        // 5. Permintaan PENDING dari User PTP (Kabel 25, Map 30)
        $user5 = $userStaff->where('bagian_id', 5)->first();
        if ($user5) {
            $p5 = Permintaan::create([
                'user_id' => $user5->id,
                'created_at' => now()->subDays(1),
            ]);

            DetailPermintaan::create([
                'permintaan_id' => $p5->id,
                'bagian_id' => 5,
                'barang_id' => 7, // Kabel
                'jumlah' => 25,
                'approved' => 'pending',
                'created_at' => now()->subDays(1),
            ]);

            DetailPermintaan::create([
                'permintaan_id' => $p5->id,
                'bagian_id' => 5,
                'barang_id' => 8, // Map
                'jumlah' => 30,
                'approved' => 'pending',
                'created_at' => now()->subDays(1),
            ]);

            $permintaanData[] = "⏳ Permintaan #{$p5->id} (PENDING) - {$user5->name}";
        }

        $this->command->info('✅ ' . Permintaan::count() . ' permintaan berhasil dibuat');
        foreach ($permintaanData as $info) {
            $this->command->line('   ' . $info);
        }
        $this->command->info('   Detail Permintaan: ' . DetailPermintaan::count() . ' items');
        $this->command->info('   Detail Terverifikasi: ' . DetailTerverifikasi::count() . ' items');
    }
}
