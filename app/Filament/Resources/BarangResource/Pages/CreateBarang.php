<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Imports\BarangImporter;
use Filament\Forms\Components\Actions\Action;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;
    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->label('Upload Excel')
                ->color('success')
                ->icon('heroicon-m-arrow-up-tray')
                ->use(BarangImporter::class)
                ->modalHeading('Upload Excel File')
                ->modalIcon('heroicon-m-arrow-up-tray')
                ->modalDescription('Make sure the Excel file contains the columns: item_code, item_name, stock, and unit_name. If the unit name is not found, stock will not be added.')
                ->uploadField(
                    fn($upload) => $upload
                        ->label("Choose Item File (.csv/.xlsx)")
                        ->placeholder("Click to browse or drag a file here")
                        ->hintAction(
                            Action::make('downloadTemplate')
                                ->label('Download Template')
                                ->icon('heroicon-m-arrow-down-tray')
                                ->url(asset('templates/Template_Tabel_Barang.xlsx'))
                                ->openUrlInNewTab()
                        )
                )
                ->modalWidth('3xl')
                ->size('xl')
                ->after(function () {
                    return redirect(request()->header('Referer'));
                }),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Item added to catalog successfully';
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
            ->title('Warehouse stock records created automatically')
            ->success()
            ->send();
    }
}
