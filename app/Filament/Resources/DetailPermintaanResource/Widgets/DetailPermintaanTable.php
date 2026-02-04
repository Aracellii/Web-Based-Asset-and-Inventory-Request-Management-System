<?php

namespace App\Filament\Resources\DetailPermintaanResource\Widgets;

use App\Models\DetailPermintaan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class DetailPermintaanTable extends BaseWidget
{
    // Ini sangat penting: Properti untuk menerima data dari tombol "Lihat"
    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->query(
                // Filter: Hanya ambil detail milik ID permintaan ini
                DetailPermintaan::query()->where('permintaan_id', $this->record?->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Qty')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('stok_saat_ini')
                    ->label('Stok Gudang')
                    ->color('gray'),
            ])
            ->paginated(false); // Sembunyikan pagination agar tampil seperti daftar simpel
    }
}
