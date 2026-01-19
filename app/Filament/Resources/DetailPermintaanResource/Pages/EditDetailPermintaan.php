<?php

namespace App\Filament\Resources\DetailPermintaanResource\Pages;

use App\Filament\Resources\DetailPermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetailPermintaan extends EditRecord
{
    protected static string $resource = DetailPermintaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
