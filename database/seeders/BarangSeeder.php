<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama_barang' => 'Pensil'],
            ['nama_barang' => 'buku'],
            ['nama_barang' => 'Kertas A4'],
            ['nama_barang' => 'Galon'],
            ['nama_barang' => 'Dispenser'],
            ['nama_barang' => 'Binder'],
            ['nama_barang' => 'Kabel'],
            ['nama_barang' => 'Map'],
        ];

        foreach ($data as $item) {
            Barang::create($item);
        }
    }
}