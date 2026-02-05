<?php

namespace App\Filament\Resources\PermintaanResource\Pages;

use App\Filament\Resources\PermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePermintaan extends CreateRecord
{
    protected static string $resource = PermintaanResource::class;
    
    protected static ?string $title = 'Buat Permintaan';
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Buat Permintaan');
    }
}
