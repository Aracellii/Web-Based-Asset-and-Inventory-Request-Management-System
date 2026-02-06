<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Filament\Notifications\Notification;
use Exception;

class BarangImporter implements ToCollection, WithHeadingRow
{
    use Importable;

    public $successCount = 0;
    public $failedCount = 0;
    public $errors = [];
    public $rowNumber = 0;

    public function collection(Collection $rows)
    {
        try {
            // Wrap seluruh import dalam transaction untuk rollback total jika ada error
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    $this->model($row->toArray());
                }

                // Jika ada error, throw exception untuk rollback
                if (count($this->errors) > 0) {
                    throw new Exception(implode("\n", $this->errors));
                }
            });

            // Jika semua berhasil, tampilkan notifikasi sukses
            Notification::make()
                ->success()
                ->title('Import Berhasil!')
                ->body("Total {$this->successCount} barang berhasil diimport.")
                ->send();
        } catch (Exception $e) {
            // Jika ada error, tampilkan notifikasi gagal
            Notification::make()
                ->danger()
                ->title('Import Gagal!')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function model(array $row)
    {
        try {
            $this->rowNumber++;

            // 1. Ambil data dari kolom Excel
            $kodeBarang  = trim($row['kode_barang'] ?? '');
            $namaBarang  = trim($row['nama_barang'] ?? '');
            $stokInput   = (int) ($row['stok'] ?? 0);
            $namaBagian  = trim($row['nama_bagian'] ?? '');

            // Validasi data
            if (!$kodeBarang) {
                throw new Exception("Baris {$this->rowNumber}: Kode barang tidak boleh kosong");
            }
            if (!$namaBarang) {
                throw new Exception("Baris {$this->rowNumber}: Nama barang tidak boleh kosong");
            }
            if (!$namaBagian) {
                throw new Exception("Baris {$this->rowNumber}: Nama bagian tidak boleh kosong");
            }

            // 2. Simpan/Update data Barang
            $barang = Barang::withTrashed()->where('kode_barang', $kodeBarang)->first();

            if ($barang) {
                $barang->restore();
                $barang->update([
                    'nama_barang' => $namaBarang
                ]);
            } else {
                $barang = Barang::create([
                    'kode_barang' => $kodeBarang,
                    'nama_barang' => $namaBarang,
                ]);
            }

            // 3. Cari Bagian secara Case-Insensitive
            $bagian = Bagian::whereRaw('LOWER(nama_bagian) = ?', [
                strtolower($namaBagian)
            ])->first();

            if (!$bagian) {
                throw new Exception("Baris {$this->rowNumber}: Bagian '{$namaBagian}' tidak ditemukan");
            }

            // 4. Update stok di tabel Gudang
            $gudang = Gudang::firstOrNew([
                'barang_id' => $barang->id,
                'bagian_id' => $bagian->id,
            ]);

            $gudang->keteranganOtomatis = 'Pembelian';
            $gudang->stok = ($gudang->stok ?? 0) + $stokInput;
            $gudang->save();

            // 5. OTOMATIS BUAT BARANG DI SEMUA BAGIAN LAIN DENGAN STOK 0
            $semuaBagian = Bagian::all();
            foreach ($semuaBagian as $bagianLain) {
                if ($bagianLain->id === $bagian->id) {
                    continue;
                }

                $gudangLain = Gudang::where('barang_id', $barang->id)
                    ->where('bagian_id', $bagianLain->id)
                    ->first();

                if (!$gudangLain) {
                    Gudang::create([
                        'barang_id' => $barang->id,
                        'bagian_id' => $bagianLain->id,
                        'stok' => 0,
                    ]);
                }
            }

            $this->successCount++;
            return null;
        } catch (Exception $e) {
            $this->failedCount++;
            $this->errors[] = $e->getMessage();
            Log::error('BarangImporter Error: ' . $e->getMessage());
            throw $e; // Lempar error agar transaction bisa rollback
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
