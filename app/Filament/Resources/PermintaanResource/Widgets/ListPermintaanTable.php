<?php

namespace App\Filament\Resources\PermintaanResource\Widgets;

use App\Models\Permintaan;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Infolists\Components\Livewire;
use App\Filament\Resources\DetailPermintaanResource\Widgets\DetailPermintaanTable;
use App\Services\FilterService;

class ListPermintaanTable extends BaseWidget
{
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->hasPermissionTo('manage_permintaan');
    }
    protected int | string | array $columnSpan = 'full';
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        $query = Permintaan::query();

        if ($user->can('lihat_bagian_sendiri') && !$user->can('lihat_semua_bagian')) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('bagian_id', $user->bagian_id)
            );
        }

        $query->whereHas(
            'detailPermintaans',
            fn($q) =>
            $q->where('approved', 'pending')
        );

        return $query;
    }


    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('List Permintaan')
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Permintaan')
                    ->sortable()
                    ->weight('bold')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('detailPermintaans.barang.nama_barang')
                    ->label('Preview Barang')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->color('gray')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Permintaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->detailPermintaans()->count();
                        $processed = $record->detailPermintaans()
                            ->where('approved', '!=', 'pending')
                            ->count();

                        return "{$processed} / {$total}";
                    })
                    ->badge()
                    // Logika Warna: Abu-abu jika nol, Hijau jika selesai semua, Kuning jika sebagian
                    ->color(function ($state) {
                        [$processed, $total] = explode(' / ', $state);
                        if ($processed == 0) return 'gray';
                        if ($processed == $total) return 'success';
                        return 'warning';
                    })
                    // Menambahkan keterangan di bawah angka (description)
                    ->description(function ($state) {
                        [$processed, $total] = explode(' / ', $state);

                        if ($total == 0) return 'Tidak ada item';
                        if ($processed == 0) return 'Belum diproses';
                        if ($processed == $total) return 'Selesai';

                        return 'Dalam proses';
                    }),
            ])

            ->actions([
                Action::make('view_details')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalWidth('5xl')
                    ->modalHeading('Detail Permintaan')
                    ->infolist([
                        Livewire::make(DetailPermintaanTable::class, function ($record) {
                            return [
                                'record' => $record,
                                'canApproval' => true, // bisa approve/reject
                                'canAction' => false, // tidak bisa edit
                            ];
                        }),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])

            ->defaultSort('created_at', 'desc')
            ->filters([

                FilterService::dateRangeFilter('created_at'),
                
                Tables\Filters\SelectFilter::make('filter_bagian')
                    ->relationship('user.bagian', 'nama_bagian')
                    ->label('Filter Unit Kerja')
                    ->multiple(true)
                    ->preload(),
            ])
            ->emptyStateHeading('Tidak ada permintaan');
    }
}
