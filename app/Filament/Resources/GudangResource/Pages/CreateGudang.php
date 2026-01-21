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
        // Jika role KEUANGAN 
        if (Auth::user()->role === 'keuangan') {

            $bagians = Bagian::all();

            foreach ($bagians as $bagian) {
                Gudang::updateOrCreate(
                    [
                        'barang_id' => $data['barang_id'],
                        'bagian_id' => $bagian->id,
                    ],
                    [
                        'stok' => $data['stok'],
                    ]
                );
            }

            return Gudang::where('barang_id', $data['barang_id'])->first();
        }

        $data['bagian_id'] = Auth::user()->bagian_id;

        return parent::handleRecordCreation($data);
    }
}
