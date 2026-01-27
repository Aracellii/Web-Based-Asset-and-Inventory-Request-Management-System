<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogAktivitasResource\Pages;
use App\Models\LogAktivitas;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogAktivitasResource extends Resource
{
    protected static ?int $navigationSort = 5;
    protected static ?string $model = LogAktivitas::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // Hak Akses: Mematikan fungsi Create, Edit, dan Delete
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
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Gunakan static::getModel()::query() untuk memutus hubungan dengan parent jika perlu
        $query = static::getModel()::query();

        // 1. Keuangan: Akses Mutlak
        if ($user->role === 'keuangan') {
            return $query;
        }

        // 2. Admin Gudang: Ambil semua log milik user yang satu bagian dengannya
        if ($user->role === 'admin') {
            $bagianId = $user->bagian_id;

            // Jika admin tidak punya bagian_id, jangan tampilkan data (safety first)
            if (!$bagianId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('user_id', function ($q) use ($bagianId) {
                $q->select('id')
                    ->from('users')
                    ->where('bagian_id', $bagianId)
                    ->where('role', '!=', 'keuangan');
            });
        }

        // 3. Staff / User Biasa: Hanya log miliknya sendiri
        return $query->where('user_id', $user->id);
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
                    ->description(fn($record) => "Kode: {$record->kode_barang_snapshot}")
                    ->searchable(),

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
                    ->color(function ($record) {
                        return $record->stok_akhir < $record->stok_awal ? 'danger' : 'success';
                    })
                    ->formatStateUsing(function ($record, $state) {
                        $simbol = $record->stok_akhir < $record->stok_awal ? '-' : '+';
                        return "{$simbol} {$state}";
                    }),

                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Stok Awal')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Stok Akhir')
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
