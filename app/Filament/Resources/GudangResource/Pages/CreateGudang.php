<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use App\Models\LogAktivitas;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Database\Eloquent\Model;

class CreateGudang extends CreateRecord
{
    protected static string $resource = GudangResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // ROLE KEUANGAN
        if (Auth::user()->role === 'keuangan') {

            $bagians = Bagian::all();
            $stokInput = (int) ($data['stok'] ?? 0);
            
            // Fetch the barang record to get the kode_barang
            $barang = \App\Models\Barang::find($data['barang_id']);

            foreach ($bagians as $bagian) {
                $gudang = Gudang::firstOrCreate(
                    [
                        'barang_id' => $data['barang_id'],
                        'bagian_id' => $bagian->id,
                    ],
                    [
                        'stok' => 0,
                    ]
                );

                // Cek apakah ada penambahan stok
                if ($stokInput > 0) {
                    $stokAwal = $gudang->stok;
                    $gudang->increment('stok', $stokInput);

                    // Catat ke log_aktivitas untuk setiap bagian
                    LogAktivitas::create([
                        'barang_id' => $data['barang_id'],
                        'user_id' => Auth::id(),
                        'gudang_id' => $gudang->id,
                        'nama_barang_snapshot' => $barang->nama_barang,
                        'kode_barang_snapshot' => $barang->kode_barang,
                        'user_snapshot' => Auth::user()->name,
                        'nama_bagian_snapshot' => $bagian->nama_bagian,
                        'tipe' => 'masuk',
                        'jumlah' => $stokInput,
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $stokAwal + $stokInput,
                    ]);
                }
            }

            return Gudang::where('barang_id', $data['barang_id'])->first();
        }

        // Default logic for other roles...
        $data['bagian_id'] = Auth::user()->bagian_id;
        return parent::handleRecordCreation($data);
    }
}
