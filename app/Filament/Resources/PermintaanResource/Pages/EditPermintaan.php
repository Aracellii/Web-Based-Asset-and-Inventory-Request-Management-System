<?php

namespace App\Filament\Resources\PermintaanResource\Pages;

use App\Filament\Resources\PermintaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPermintaan extends EditRecord
{
    protected static string $resource = PermintaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(function () {
                    // Cek apakah semua detail permintaan berstatus pending
                    return $this->record->detailPermintaans()->where('approved', 'pending')->count() == 
                           $this->record->detailPermintaans()->count();
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Cek apakah ada detail yang bukan pending
        $nonPendingCount = $this->record->detailPermintaans()->where('approved', '!=', 'pending')->count();
        
        if ($nonPendingCount > 0) {
            Notification::make()
                ->warning()
                ->title('Peringatan')
                ->body('Permintaan ini tidak dapat diedit karena sudah diproses (approved/rejected).')
                ->persistent()
                ->send();
            
            redirect($this->getResource()::getUrl('index'));
        }
    }
}
