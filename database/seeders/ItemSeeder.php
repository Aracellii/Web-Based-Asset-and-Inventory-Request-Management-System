<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📦 Seeding item catalog...');
        
        $data = [
            ['nama_barang' => 'Pencil', 'kode_barang' => 'B001'],
            ['nama_barang' => 'Book', 'kode_barang' => 'B002'],
            ['nama_barang' => 'A4 Paper', 'kode_barang' => 'B003'],
            ['nama_barang' => 'Water Container', 'kode_barang' => 'B004'],
            ['nama_barang' => 'Water Dispenser', 'kode_barang' => 'B005'],
            ['nama_barang' => 'Binder', 'kode_barang' => 'B006'],
            ['nama_barang' => 'Cable', 'kode_barang' => 'B007'],
            ['nama_barang' => 'Folder', 'kode_barang' => 'B008'],
        ];

        foreach ($data as $item) {
            Barang::create($item);
        }
        
        $this->command->info('✅ ' . count($data) . ' items created successfully');
    }
}