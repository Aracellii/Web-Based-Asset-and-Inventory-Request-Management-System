<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Filament\Notifications\Notification;
use Exception;

class BarangImporter implements ToCollection, WithHeadingRow
{
    use Importable;

    public $successCount = 0;
    public $rowNumber = 0;
    protected $daftarBagian;

    public function collection(Collection $rows)
    {
        // 1. CACHING: Ambil semua bagian sekali saja di awal
        // Ini kunci agar import 1000 data tidak lemot/timeout
        $this->daftarBagian = Bagian::all();

        if ($this->daftarBagian->isEmpty()) {
            Notification::make()->danger()->title('Gagal!')->body('Data Bagian tidak ditemukan.')->send();
            return;
        }

        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    $this->prosesBaris($row);
                }
            });

            Notification::make()
                ->success()
                ->title('Import Berhasil!')
                ->body("Total {$this->successCount} barang diproses. Log aktivitas tercatat.")
                ->send();

        } catch (Exception $e) {
            Log::error('BarangImporter Error: ' . $e->getMessage());
            Notification::make()
                ->danger()
                ->title('Import Gagal!')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }

    private function prosesBaris($row)
    {
        $this->rowNumber++;

        // Ambil data kolom excel
        $kodeBarang = trim($row['kode_barang'] ?? '');
        $namaBarang = trim($row['nama_barang'] ?? '');
        $stokInput  = (int) ($row['stok'] ?? 0);
        $namaBagianInput = trim($row['nama_bagian'] ?? '');

        // Validasi Dasar
        if (!$kodeBarang || !$namaBarang || !$namaBagianInput) {
            throw new Exception("Baris {$this->rowNumber}: Kode/Nama/Bagian tidak boleh kosong.");
        }

        // 2. SIMPAN BARANG
        // Menggunakan Eloquent agar event booted() di model Barang tetap terpanggil
        $barang = Barang::withTrashed()->updateOrCreate(
            ['kode_barang' => $kodeBarang],
            ['nama_barang' => $namaBarang]
        );
        if ($barang->trashed()) $barang->restore();

        // 3. CARI BAGIAN (Dari Memori/Cache)
        $bagianTujuan = $this->daftarBagian->first(fn($item) => 
            strtolower($item->nama_bagian) === strtolower($namaBagianInput)
        );

        if (!$bagianTujuan) {
            throw new Exception("Baris {$this->rowNumber}: Bagian '{$namaBagianInput}' tidak terdaftar.");
        }

        // 4. UPDATE STOK UTAMA (Memicu booted() model Gudang)
        // Kita gunakan save() agar model event 'saved' atau 'updated' ketrigger
        $gudangUtama = Gudang::firstOrNew([
            'barang_id' => $barang->id,
            'bagian_id' => $bagianTujuan->id,
        ]);

        // Perbaikan TypeError: Pastikan stok lama dikonversi ke int sebelum dijumlah
        $stokLama = (int) ($gudangUtama->stok ?? 0);
        $gudangUtama->stok = $stokLama + $stokInput;
        $gudangUtama->keteranganOtomatis = 'Pembelian';
        $gudangUtama->save(); 

        // 5. INISIALISASI BAGIAN LAIN (Memicu booted() model Gudang)
        foreach ($this->daftarBagian as $b) {
            if ($b->id === $bagianTujuan->id) continue;

            // firstOrCreate menjamin data ada, dan memicu event 'created' jika baru dibuat
            Gudang::firstOrCreate(
                ['barang_id' => $barang->id, 'bagian_id' => $b->id],
                ['stok' => 0, 'keteranganOtomatis' => 'Inisialisasi Sistem']
            );
        }

        $this->successCount++;
    }
}