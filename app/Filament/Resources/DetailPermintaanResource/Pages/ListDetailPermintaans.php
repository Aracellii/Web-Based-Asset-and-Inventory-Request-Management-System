<?php

namespace App\Filament\Resources\DetailPermintaanResource\Pages;

use App\Filament\Resources\DetailPermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailPermintaans extends ListRecords
{
    protected static string $resource = DetailPermintaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
