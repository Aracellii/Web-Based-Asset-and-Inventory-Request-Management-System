<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogAktivitasResource\Pages;
use App\Models\LogAktivitas;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LogAktivitasResource extends Resource
{
    protected static ?int $navigationSort = 5;
    protected static ?string $model = LogAktivitas::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // Hak Akses: Mematikan fungsi Create, Edit, dan Delete
    public static function canViewAny(): bool
    {
        return auth()->user()->role === 'keuangan';
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_barang_snapshot')
                    ->label('Barang')
                    ->searchable()
                    ->description(fn($record) => "Kode: {$record->kode_barang_snapshot}"),

                Tables\Columns\TextColumn::make('nama_bagian_snapshot')
                    ->label('Bidang / Bagian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'masuk' => 'success',
                        'keluar' => 'danger',
                        'penyesuaian' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Mutasi')
                    ->weight('bold')
                    ->color(fn($record) => strtolower($record->tipe ?? '') === 'keluar' ? 'danger' : 'success')
                    ->formatStateUsing(fn($record, $state) => (strtolower($record->tipe ?? '') === 'keluar' ? '-' : '+') . $state),

                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Awal')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Akhir')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user_snapshot')
                    ->label('Oleh')
                    ->description(fn($record) => "ID: " . ($record->user_id ?? 'System')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'masuk' => 'Masuk',
                        'keluar' => 'Keluar',
                        'penyesuaian' => 'Penyesuaian',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogAktivitas::route('/'),
        ];
    }
}
