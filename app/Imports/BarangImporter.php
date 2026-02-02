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
            $gudang->keteranganOtomatis = 'Pembelian';
            $gudang->stok = ($gudang->stok ?? 0) + (int) $stokInput;
            $gudang->save();

            // 5. OTOMATIS BUAT BARANG DI SEMUA BAGIAN LAIN DENGAN STOK 0
            $semuaBagian = Bagian::all();
            foreach ($semuaBagian as $bagianLain) {
                // Skip bagian yang sudah diproses
                if ($bagianLain->id === $bagian->id) {
                    continue;
                }

                // Cek apakah barang sudah ada di bagian lain
                $gudangLain = Gudang::where('barang_id', $barang->id)
                    ->where('bagian_id', $bagianLain->id)
                    ->first();

                // Jika belum ada, buat dengan stok 0
                if (!$gudangLain) {
                    Gudang::create([
                        'barang_id' => $barang->id,
                        'bagian_id' => $bagianLain->id,
                        'stok' => 0,
                    ]);
                }
            }
        }

        // Return null karena kita sudah handle simpan data secara manual di atas
        return null;
    }
    public function chunkSize(): int
    {
        return 100; // Proses 100 baris per satu waktu
    }
}
