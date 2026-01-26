<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
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
                    $gudang->stok += $stokInput;
                    $gudang->save();
                }
            }

            return Gudang::where('barang_id', $data['barang_id'])->first();
        }

        // Default logic for other roles...
        $data['bagian_id'] = Auth::user()->bagian_id;
        return parent::handleRecordCreation($data);
    }
}
