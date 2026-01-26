<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBarang extends ViewRecord
{
    protected static string $resource = BarangResource::class;

    protected static ?string $title = 'Lihat Katalog Barang';

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
