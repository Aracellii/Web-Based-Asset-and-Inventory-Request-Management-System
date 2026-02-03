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
                ->label('Import dari Excel')
                ->color('success')
                ->use(BarangImporter::class)
                ->modalHeading('Tambahkan barang sekaligus dari file Excel')
                ->modalDescription('Pastikan dalam file excel terdapat kolom: kode_barang, nama_barang, stok, dan nama_bagian. Jika nama_bagian tidak ditemukan, stok tidak akan ditambahkan.')
                ->uploadField(
                    fn($upload) => $upload
                        ->label("Pilih File Barang (.csv/.xlsx)")
                        ->placeholder("Klik untuk cari atau Seret file ke sini")
                        ->hintAction(
                            Action::make('downloadTemplate')
                                ->label('Download Template')
                                ->icon('heroicon-m-arrow-down-tray')
                                ->url(asset('templates/Template_Tabel_Barang.xlsx'))
                                ->openUrlInNewTab()
                        )
                )
                ->modalWidth('3xl')
                ->size('xl'),
        ];
    }
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
