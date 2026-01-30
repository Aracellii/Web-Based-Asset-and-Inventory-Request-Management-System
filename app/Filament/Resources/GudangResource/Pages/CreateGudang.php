<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Gudang;
use Illuminate\Database\Eloquent\Model;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Imports\BarangImporter;

class CreateGudang extends CreateRecord
{
    protected static string $resource = GudangResource::class;
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
                )
                ->modalWidth('3xl')
                ->size('xl'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        // ROLE KEUANGAN
        if (Auth::user()->role === 'keuangan') {

            $bagianIds = $data['bagian_ids'] ?? [];
            $stokInput = (int) ($data['stok'] ?? 0);

            foreach ($bagianIds as $bagianId) {
                $gudang = Gudang::firstOrCreate(
                    [
                        'barang_id' => $data['barang_id'],
                        'bagian_id' => $bagianId,
                    ],
                    [
                        'stok' => 0,
                    ]
                );

                // penambahan stok
                if ($stokInput > 0) {
                    $gudang->keteranganOtomatis = 'Pembelian';
                    $gudang->stok += $stokInput;
                    $gudang->save();
                }
            }

            return Gudang::where('barang_id', $data['barang_id'])->first();
        }
        $data['bagian_id'] = Auth::user()->bagian_id;
        return parent::handleRecordCreation($data);
    }
}
