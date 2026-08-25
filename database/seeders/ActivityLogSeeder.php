<?php

namespace Database\Seeders;

use App\Models\LogAktivitas;
use App\Models\Gudang;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{

    /**
     * Run the database seeds.
     * 
    * Create sample activity logs for demo:
    * - Inbound items
    * - Outbound items
    * - Stock adjustments
     */
    
    public function run(): void
    {
        $this->command->info('📝 Seeding activity logs...');

        $keuanganRole = \Spatie\Permission\Models\Role::where('name', 'keuangan')->first();
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $userRole = \Spatie\Permission\Models\Role::where('name', 'user')->first();

        $adminKeuangan = User::where('role_id', $keuanganRole->id)->first();
        $adminTU = User::where('role_id', $adminRole->id)->where('bagian_id', 1)->first();
        $userTU = User::where('role_id', $userRole->id)->where('bagian_id', 1)->first();

        $logs = [];

        // 1. Inbound log - Finance admin adds Pencil stock in General Administration
        $gudang1 = Gudang::where('bagian_id', 1)->where('barang_id', 1)->first();
        if ($gudang1 && $adminKeuangan) {
            $stokLama = $gudang1->stok - 10;
            LogAktivitas::create([
                'barang_id' => 1,
                'user_id' => $adminKeuangan->id,
                'gudang_id' => $gudang1->id,
                'nama_barang_snapshot' => 'Pencil',
                'kode_barang_snapshot' => 'B001',
                'user_snapshot' => $adminKeuangan->name,
                'nama_bagian_snapshot' => 'General Administration',
                'tipe' => 'Inbound',
                'jumlah' => 10,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang1->stok,
                'keterangan' => 'New stock purchase',
                'created_at' => now()->subDays(15),
            ]);
            $logs[] = 'Inbound: +10 Pencil (GA)';
        }

        // 2. Outbound log - GA user takes Pencil
        if ($gudang1 && $userTU) {
            $stokLama = $gudang1->stok + 5;
            LogAktivitas::create([
                'barang_id' => 1,
                'user_id' => $userTU->id,
                'gudang_id' => $gudang1->id,
                'nama_barang_snapshot' => 'Pencil',
                'kode_barang_snapshot' => 'B001',
                'user_snapshot' => $userTU->name,
                'nama_bagian_snapshot' => 'General Administration',
                'tipe' => 'Outbound',
                'jumlah' => 5,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang1->stok,
                'keterangan' => 'Request approved',
                'created_at' => now()->subDays(10),
            ]);
            $logs[] = 'Outbound: -5 Pencil (GA)';
        }

        // 3. Inbound log - GA admin adds Book stock
        $gudang2 = Gudang::where('bagian_id', 1)->where('barang_id', 2)->first();
        if ($gudang2 && $adminTU) {
            $stokLama = $gudang2->stok - 20;
            LogAktivitas::create([
                'barang_id' => 2,
                'user_id' => $adminTU->id,
                'gudang_id' => $gudang2->id,
                'nama_barang_snapshot' => 'Book',
                'kode_barang_snapshot' => 'B002',
                'user_snapshot' => $adminTU->name,
                'nama_bagian_snapshot' => 'General Administration',
                'tipe' => 'Inbound',
                'jumlah' => 20,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang2->stok,
                'keterangan' => 'Monthly routine procurement',
                'created_at' => now()->subDays(12),
            ]);
            $logs[] = 'Inbound: +20 Book (GA)';
        }

        // 4. Adjustment log - Finance admin adjusts A4 Paper stock
        $gudang3 = Gudang::where('bagian_id', 2)->where('barang_id', 3)->first();
        if ($gudang3 && $adminKeuangan) {
            $stokLama = $gudang3->stok - 5;
            LogAktivitas::create([
                'barang_id' => 3,
                'user_id' => $adminKeuangan->id,
                'gudang_id' => $gudang3->id,
                'nama_barang_snapshot' => 'A4 Paper',
                'kode_barang_snapshot' => 'B003',
                'user_snapshot' => $adminKeuangan->name,
                'nama_bagian_snapshot' => 'Survey and Mapping',
                'tipe' => 'Adjustment',
                'jumlah' => 5,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang3->stok,
                'keterangan' => 'Stock count correction',
                'created_at' => now()->subDays(8),
            ]);
            $logs[] = 'Adjustment: +5 A4 Paper (SM)';
        }

        // 5. Outbound log - admin reduces Water Container stock
        $gudang4 = Gudang::where('bagian_id', 3)->where('barang_id', 4)->first();
        $adminPHP = User::where('role_id', $adminRole->id)->where('bagian_id', 3)->first();
        if ($gudang4 && $adminPHP) {
            $stokLama = $gudang4->stok + 3;
            LogAktivitas::create([
                'barang_id' => 4,
                'user_id' => $adminPHP->id,
                'gudang_id' => $gudang4->id,
                'nama_barang_snapshot' => 'Water Container',
                'kode_barang_snapshot' => 'B004',
                'user_snapshot' => $adminPHP->name,
                'nama_bagian_snapshot' => 'Rights Determination and Registration',
                'tipe' => 'Outbound',
                'jumlah' => 3,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang4->stok,
                'keterangan' => 'Routine usage',
                'created_at' => now()->subDays(5),
            ]);
            $logs[] = 'Outbound: -3 Water Container (RDR)';
        }

        // 6. Inbound log - finance adds Water Dispenser stock
        $gudang5 = Gudang::where('bagian_id', 4)->where('barang_id', 5)->first();
        if ($gudang5 && $adminKeuangan) {
            $stokLama = $gudang5->stok - 15;
            LogAktivitas::create([
                'barang_id' => 5,
                'user_id' => $adminKeuangan->id,
                'gudang_id' => $gudang5->id,
                'nama_barang_snapshot' => 'Water Dispenser',
                'kode_barang_snapshot' => 'B005',
                'user_snapshot' => $adminKeuangan->name,
                'nama_bagian_snapshot' => 'Planning and Empowerment',
                'tipe' => 'Inbound',
                'jumlah' => 15,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang5->stok,
                'keterangan' => 'Purchase of new asset',
                'created_at' => now()->subDays(3),
            ]);
            $logs[] = 'Inbound: +15 Water Dispenser (PE)';
        }

        // 7. Adjustment log - admin reduces damaged stock
        $gudang6 = Gudang::where('bagian_id', 5)->where('barang_id', 6)->first();
        $adminPTP = User::where('role_id', $adminRole->id)->where('bagian_id', 5)->first();
        if ($gudang6 && $adminPTP) {
            $stokLama = $gudang6->stok + 2;
            LogAktivitas::create([
                'barang_id' => 6,
                'user_id' => $adminPTP->id,
                'gudang_id' => $gudang6->id,
                'nama_barang_snapshot' => 'Binder',
                'kode_barang_snapshot' => 'B006',
                'user_snapshot' => $adminPTP->name,
                'nama_bagian_snapshot' => 'Land Procurement and Development',
                'tipe' => 'Adjustment',
                'jumlah' => -2,
                'stok_awal' => $stokLama,
                'stok_akhir' => $gudang6->stok,
                'keterangan' => 'Damaged / missing item',
                'created_at' => now()->subDays(2),
            ]);
            $logs[] = 'Adjustment: -2 damaged Binder (LPD)';
        }

        $count = LogAktivitas::count();
        $this->command->info("✅ {$count} activity logs created successfully");
        foreach ($logs as $log) {
            $this->command->line('   ' . $log);
        }
    }
}
