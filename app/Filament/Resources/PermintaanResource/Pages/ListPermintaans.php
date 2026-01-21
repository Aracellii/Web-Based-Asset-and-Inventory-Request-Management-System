<?php

namespace App\Filament\Resources\PermintaanResource\Pages;

use App\Filament\Resources\PermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListPermintaans extends ListRecords
{
    protected static string $resource = PermintaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PermintaanResource\Widgets\ListPermintaanTable::class,
        ];
    }

    #[On('refreshPermintaanSaya')] 
    public function refreshTable()
    {
        // Fungsi ini akan menangkap sinyal dari Widget 
        // dan memaksa tabel di Resource untuk refresh secara "Live"
    }
}
