<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use App\Models\BarangMasuk;
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
        $stokLama = Gudang::find($this->record->id)?->stok ?? 0;
        $stokBaru = (int) ($data['stok'] ?? 0);
        $selisih = $stokBaru - $stokLama;

        // Catat ke barang_masuks hanya jika stok bertambah
        if ($selisih > 0) {
            BarangMasuk::create([
                'barang_id' => $this->record->barang_id,
                'bagian_id' => $this->record->bagian_id,
                'user_id' => Auth::id(),
                'jumlah' => $selisih,
                'tanggal_masuk' => now()->toDateString(),
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
