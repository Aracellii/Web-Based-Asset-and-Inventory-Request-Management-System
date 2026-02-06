<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;

class ExcelImportService
{
    public static function importBarang(Collection $rows)
    {
        ignore_user_abort(true);
        set_time_limit(300);

        return DB::transaction(function () use ($rows) {
            $semuaBagian = Bagian::all();
            $successCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index;

                try {
                    $kodeBarang  = trim($row['kode_barang'] ?? '');
                    $namaBarang  = trim($row['nama_barang'] ?? '');
                    $stokInput   = (int) ($row['stok'] ?? 0);
                    $namaBagian  = trim($row['nama_bagian'] ?? '');

                    if (!$kodeBarang || !$namaBarang || !$namaBagian) {
                        throw new Exception("Data tidak lengkap di baris {$rowNumber}");
                    }

                    $barang = Barang::withTrashed()->updateOrCreate(
                        ['kode_barang' => $kodeBarang],
                        ['nama_barang' => $namaBarang, 'deleted_at' => null]
                    );

                    $bagianTujuan = $semuaBagian->first(
                        fn($b) =>
                        strtolower($b->nama_bagian) === strtolower($namaBagian)
                    );

                    if (!$bagianTujuan) {
                        throw new Exception("Bagian '{$namaBagian}' tidak ditemukan.");
                    }

                    $gudang = Gudang::firstOrNew([
                        'barang_id' => $barang->id,
                        'bagian_id' => $bagianTujuan->id,
                    ]);

                    $gudang->stok = ($gudang->stok ?? 0) + $stokInput;
                    $gudang->save();

                    $dataStokNol = $semuaBagian->filter(fn($b) => $b->id !== $bagianTujuan->id)
                        ->map(fn($b) => [
                            'barang_id' => $barang->id,
                            'bagian_id' => $b->id,
                            'stok'      => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->toArray();

                    if (!empty($dataStokNol)) {
                        Gudang::insertOrIgnore($dataStokNol);
                    }

                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                    throw new Exception("Gagal di Baris {$rowNumber}: " . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }

            return [
                'success' => true,
                'count' => $successCount,
                'message' => "Total {$successCount} barang berhasil diproses."
            ];
        });
    }
}
