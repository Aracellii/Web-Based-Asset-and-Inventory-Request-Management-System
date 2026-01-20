<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Gudang;

class CreateGudang extends CreateRecord
{
    protected static string $resource = GudangResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return Gudang::updateOrCreate(
            [
                'barang_id' => $data['barang_id'],
                'bagian_id' => $data['bagian_id'],
            ],
            [
                'stok' => $data['stok'],
            ]
        );
    }
}
