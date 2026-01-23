<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Barang berhasil ditambahkan ke katalog';
    }

    protected function afterCreate(): void
    {
        // Otomatis buat record gudang untuk setiap bagian dengan stok 0
        $bagians = Bagian::all();
        
        foreach ($bagians as $bagian) {
            Gudang::firstOrCreate([
                'barang_id' => $this->record->id,
                'bagian_id' => $bagian->id,
            ], [
                'stok' => 0,
            ]);
        }

        Notification::make()
            ->title('Stok gudang otomatis dibuat')
            ->success()
            ->send();
    }
}
