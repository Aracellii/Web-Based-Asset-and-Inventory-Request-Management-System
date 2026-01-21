<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gudang;


class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        $stok_per_barang = [1 => 5, 2 => 15, 3 => 30, 4 => 30, 5 => 30, 6 => 30, 7 => 30, 8 => 30];

        foreach (range(1, 6) as $bagian_id) {
            foreach ($stok_per_barang as $barang_id => $stok) {
                $data[] = [
                    'bagian_id' => $bagian_id,
                    'barang_id' => $barang_id,
                    'stok'      => $stok,
                ];
            }
        }

        foreach ($data as $item) {
            Gudang::create($item);
        }
    }
}
