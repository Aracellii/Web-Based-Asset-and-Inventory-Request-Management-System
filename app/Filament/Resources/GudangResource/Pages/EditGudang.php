<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use App\Models\LogAktivitas;
use App\Models\Gudang;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditGudang extends EditRecord
{
    protected static string $resource = GudangResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ambil stok lama langsung dari database
        $gudang = Gudang::find($this->record->id);
        $stokLama = $gudang?->stok ?? 0;
        $stokBaru = (int) ($data['stok'] ?? 0);
        $selisih = $stokBaru - $stokLama;

        // Catat ke log_aktivitas jika ada perubahan stok
        if ($selisih != 0) {
            $barang = $gudang->barang;
            $bagian = $gudang->bagian;

            LogAktivitas::create([
                'barang_id' => $this->record->barang_id,
                'user_id' => Auth::id(),
                'gudang_id' => $this->record->id,
                'nama_barang_snapshot' => $barang->nama_barang ?? '',
                'kode_barang_snapshot' => $barang->kode_barang ?? '',
                'user_snapshot' => Auth::user()->name,
                'nama_bagian_snapshot' => $bagian->nama_bagian ?? '',
                'tipe' => $selisih > 0 ? 'masuk' : 'keluar',
                'jumlah' => abs($selisih),
                'stok_awal' => $stokLama,
                'stok_akhir' => $stokBaru,
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
