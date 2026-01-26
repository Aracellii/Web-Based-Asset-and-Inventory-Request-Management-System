<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use App\Models\BarangMasuk;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Bagian;
use App\Models\Gudang;
use Illuminate\Database\Eloquent\Model;

class CreateGudang extends CreateRecord
{
    protected static string $resource = GudangResource::class;

    protected static ?string $title = 'Tambah Stok Barang';

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
                    $gudang->increment('stok', $stokInput);

                    // Catat ke barang_masuks untuk setiap bagian
                    BarangMasuk::create([
                        'barang_id' => $data['barang_id'],
                        'bagian_id' => $bagian->id,
                        'user_id' => Auth::id(),
                        'jumlah' => $stokInput,
                        'tanggal_masuk' => now()->toDateString(),
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
