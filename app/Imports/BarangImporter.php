<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Penting: Agar bisa baca nama kolom

class BarangImporter implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Ambil data dari kolom Excel (sesuaikan nama kolom di excel kamu)
        $kodeBarang  = $row['kode_barang'] ?? null;
        $namaBarang  = $row['nama_barang'] ?? null;
        $stokInput   = $row['stok'] ?? 0;
        $namaBagian  = $row['nama_bagian'] ?? null;

        if (!$kodeBarang) return null;
        
        // 2. Simpan/Update data Barang
        $barang = Barang::withTrashed()->where('kode_barang', $kodeBarang)->first();

        if ($barang) {
            // Jika ketemu (meskipun statusnya dihapus), kita hidupkan lagi (restore) dan update namanya
            $barang->restore();
            $barang->update([
                'nama_barang' => $row['nama_barang'] ?? $barang->nama_barang
            ]);
        } else {
            $barang = Barang::create([
                'kode_barang' => $kodeBarang,
                'nama_barang' => $namaBarang,
            ]);
        }
        // Ini memastikan barang baru punya baris di setiap bagian dengan stok default 0
        $allBagians = Bagian::all();
        foreach ($allBagians as $b) {
            Gudang::firstOrCreate([
                'barang_id' => $barang->id,
                'bagian_id' => $b->id,
            ], [
                'stok' => 0,
            ]);
        }

        // 3. Cari Bagian secara Case-Insensitive (LOWER)
        $bagian = Bagian::whereRaw('LOWER(nama_bagian) = ?', [
            strtolower($namaBagian)
        ])->first();

        // 4. Jika Bagian ditemukan, update stok di tabel Gudang
        if ($bagian) {
            $gudang = Gudang::firstOrNew([
                'barang_id' => $barang->id,
                'bagian_id' => $bagian->id,
            ]);

            // Tambahkan stok lama dengan stok baru dari Excel
            $gudang->stok = ($gudang->stok ?? 0) + (int) $stokInput;
            $gudang->save();
        }

        // Return null karena kita sudah handle simpan data secara manual di atas
        return null;
    }
}
