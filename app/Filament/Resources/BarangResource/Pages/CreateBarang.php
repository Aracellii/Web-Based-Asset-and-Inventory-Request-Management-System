<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Resources\Pages\CreateRecord;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;

    protected function afterCreate(): void
    {
        // Otomatis buat data gudang untuk semua bidang dengan stok 0
        $bagians = Bagian::all();

        foreach ($bagians as $bagian) {
            Gudang::create([
                'barang_id' => $this->record->id,
                'bagian_id' => $bagian->id,
                'stok' => 0,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
