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
        $data = [
            ['bagian_id' => 1, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 1, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 1, 'barang_id' => 3, 'stok' => 30],

            ['bagian_id' => 2, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 2, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 2, 'barang_id' => 3, 'stok' => 30],

            ['bagian_id' => 3, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 3, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 3, 'barang_id' => 3, 'stok' => 30],
            
            ['bagian_id' => 4, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 4, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 4, 'barang_id' => 3, 'stok' => 30],

            ['bagian_id' => 5, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 5, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 5, 'barang_id' => 3, 'stok' => 30],

            ['bagian_id' => 6, 'barang_id' => 1, 'stok' => 5],
            ['bagian_id' => 6, 'barang_id' => 2, 'stok' => 15],
            ['bagian_id' => 6, 'barang_id' => 3, 'stok' => 30],
        ];

        foreach ($data as $item) {
            Gudang::create($item);
        }
    }
}
