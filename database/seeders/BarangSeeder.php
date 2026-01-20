<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_barang' => 'Pensil', 'kode_barang' => 'B001'],
            ['nama_barang' => 'buku', 'kode_barang' => 'B002'],
            ['nama_barang' => 'Kertas A4', 'kode_barang' => 'B003'],
            ['nama_barang' => 'Galon', 'kode_barang' => 'B004'],
            ['nama_barang' => 'Dispenser', 'kode_barang' => 'B005'],
            ['nama_barang' => 'Binder', 'kode_barang' => 'B006'],
            ['nama_barang' => 'Kabel', 'kode_barang' => 'B007'],
            ['nama_barang' => 'Map', 'kode_barang' => 'B008'],
        ];

        foreach ($data as $item) {
            Barang::create($item);
        }
    }
}