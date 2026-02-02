<?php

namespace App\Filament\Resources\DetailPermintaanResource\Pages;

use App\Filament\Resources\DetailPermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PermintaanResource;

class EditDetailPermintaan extends EditRecord
{
    protected static string $resource = DetailPermintaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Kembali ke daftar permintaan utama setelah edit selesai
        return PermintaanResource::getUrl('index');
    }
}
