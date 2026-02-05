<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use App\Models\Gudang;
use App\Models\Bagian;
use Illuminate\Database\Eloquent\Model;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Imports\BarangImporter;
use Filament\Forms\Components\Actions\Action;

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
                ->modalDescription('Pastikan dalam file excel terdapat kolom: kode_barang, nama_barang, stok, dan nama_bagian. Semua baris harus valid atau import akan ditolak total.')
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

    protected function handleRecordCreation(array $data): Model
    {
        // ROLE KEUANGAN atau SUPER ADMIN
        $user = Auth::user();

        if ($user->hasRole('keuangan') || $user->hasRole('superadmin')) {

            $bagianIds = $data['bagian_ids'] ?? [];
            $stokInput = (int) ($data['stok'] ?? 0);

            // Jika bagian_ids kosong, gunakan semua bagian
            if (empty($bagianIds)) {
                $bagianIds = Bagian::pluck('id')->toArray();
            }

            $firstGudang = null;

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

                // Simpan gudang pertama untuk return
                if ($firstGudang === null) {
                    $firstGudang = $gudang;
                }
            }

            // OTOMATIS BUAT DI SEMUA BAGIAN LAIN DENGAN STOK 0
            $semuaBagian = Bagian::all();
            foreach ($semuaBagian as $bagian) {
                // Skip jika sudah diproses di atas
                if (in_array($bagian->id, $bagianIds)) {
                    continue;
                }

                // Buat gudang dengan stok 0 jika belum ada
                Gudang::firstOrCreate(
                    [
                        'barang_id' => $data['barang_id'],
                        'bagian_id' => $bagian->id,
                    ],
                    [
                        'stok' => 0,
                    ]
                );
            }

            return $firstGudang ?? Gudang::where('barang_id', $data['barang_id'])->first();
        }

        // ROLE SELAIN KEUANGAN
        // Gunakan bagian_id dari form, atau fallback ke bagian user
        $bagianId = $data['bagian_id'] ?? Auth::user()->bagian_id;
        $stokInput = (int) ($data['stok'] ?? 0);

        // Cari atau buat gudang untuk bagian yang dipilih
        $gudang = Gudang::firstOrCreate(
            [
                'barang_id' => $data['barang_id'],
                'bagian_id' => $bagianId,
            ],
            [
                'stok' => 0,
            ]
        );

        // Penambahan stok
        if ($stokInput > 0) {
            $gudang->keteranganOtomatis = 'Pembelian';
            $gudang->stok += $stokInput;
            $gudang->save();
        }

        // OTOMATIS BUAT DI SEMUA BAGIAN LAIN DENGAN STOK 0
        $semuaBagian = Bagian::all();
        foreach ($semuaBagian as $bagian) {
            // Skip bagian yang sudah diproses
            if ($bagian->id === $bagianId) {
                continue;
            }

            // Buat gudang dengan stok 0 jika belum ada
            Gudang::firstOrCreate(
                [
                    'barang_id' => $data['barang_id'],
                    'bagian_id' => $bagian->id,
                ],
                [
                    'stok' => 0,
                ]
            );
        }

        return $gudang;
    }
}
