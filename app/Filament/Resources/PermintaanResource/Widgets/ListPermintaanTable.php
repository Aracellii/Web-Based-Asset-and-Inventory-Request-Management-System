<?php

namespace App\Filament\Resources\PermintaanResource\Widgets;

use App\Models\DetailPermintaan;
use App\Models\DetailTerverifikasi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Forms\Components\Select;



class ListPermintaanTable extends BaseWidget
{
    // Widget ini HANYA muncul jika admin
    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
    protected int | string | array $columnSpan = 'full';
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = DetailPermintaan::query();

        // Filter berdasarkan role admin dan bagian yang sesuai
        if ($user->role === 'admin') {
            $query->whereHas('permintaan.user', function ($q) use ($user) {
                $q->where('users.bagian_id', $user->bagian_id);
            });
        }
        return $query;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('List Permintaan')
            ->columns([
                Tables\Columns\TextColumn::make('permintaan_id')
                    ->label('ID Permintaan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('permintaan.user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('permintaan.tanggal_permintaan')
                    ->label('Tgl Permintaan')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('permintaan.user.bagian.nama_bagian')
                    ->label('Bidang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable()
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([

                Tables\Filters\SelectFilter::make('created_at')
                    ->label('Rentang Waktu')
                    ->form([
                        Select::make('rentang')
                            ->label('Pilih Waktu')
                            ->options([
                                'all' => 'All',
                                '7' => '7 Hari Terakhir',
                                '30' => '30 Hari Terakhir',
                                '60' => '60 Hari Terakhir',
                                'this_year' => 'Tahun Ini',
                            ])
                            ->reactive()
                            ->default('all'),
                        
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['rentang'] || $data['rentang'] === 'all') {
                            return $query;
                        }

                        // Jika pilih Tahun Ini
                        if ($data['rentang'] === 'this_year') {
                            return $query->whereYear('created_at', Carbon::now()->year);
                        }

                        // Jika pilih rentang hari (7, 30, 60)
                        return $query->where('created_at', '>=', Carbon::now()->subDays((int) $data['rentang']));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['rentang'] || $data['rentang'] === 'all') {
                            return null;
                        }

                        if ($data['rentang'] === 'this_year') {
                            return 'Rentang: Tahun Ini (' . Carbon::now()->year . ')';
                        }

                        return 'Rentang: ' . $data['rentang'] . ' Hari Terakhir';
                    }),
                Tables\Filters\SelectFilter::make('approved')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Filter Status'),

                Tables\Filters\SelectFilter::make('filter_bagian')
                    ->relationship('permintaan.user.bagian', 'nama_bagian')
                    ->label('Filter Bidang'),
            ])
            ->actions([
                Action::make('approve')
                    ->visible(fn($record) => auth()->user()->role === 'admin' && $record->approved === 'pending')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Permintaan')

                    ->action(function ($record, $livewire) {
                        DB::transaction(function () use ($record) {
                            $stokGudang = $record->gudang;
                            if (!$stokGudang || $stokGudang->stok < $record->jumlah) {
                                Notification::make()
                                    ->title('Gagal Approve')
                                    ->body($stokGudang ? 'Stok tidak mencukupi!' : 'Barang tidak terdaftar di gudang bagian ini.')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                // INSERT detail_terverifikasis
                                DetailTerverifikasi::create([
                                    'detail_permintaan_id' => $record->id,
                                    'bagian_id'    => $record->bagian_id,
                                    'barang_id' => $record->barang_id,
                                    'jumlah'    => $record->jumlah,
                                    'approved'  => 'approved',
                                ]);
                                // UPDATE status approved di detail_permintaans
                                $record->update([
                                    'approved' => 'approved',
                                ]);
                                // Kurangi stok di tabel gudangs
                                $stokGudang->decrement('stok', $record->jumlah);

                                Notification::make()
                                    ->title('Permintaan berhasil di-approve')
                                    ->success()
                                    ->send();
                            }
                        });
                        $livewire->dispatch('refreshPermintaanSaya'); //refresh widget setelah approve agar status terupdate

                    }),

                Action::make('reject')
                    ->visible(fn($record) => auth()->user()->role === 'admin' && $record->approved === 'pending')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Permintaan')

                    ->action(function ($record, $livewire) {
                        DB::transaction(function () use ($record) {
                            // INSERT detail_terverifikasis
                            DetailTerverifikasi::create([
                                'detail_permintaan_id' => $record->id,
                                'bagian_id'    => $record->bagian_id,
                                'barang_id' => $record->barang_id,
                                'jumlah'    => $record->jumlah,
                                'approved'  => 'rejected',
                            ]);

                            $record->update([
                                'approved' => 'rejected',
                            ]);
                        });

                        Notification::make()
                            ->title('Permintaan berhasil di-reject')
                            ->success()
                            ->send();

                        $livewire->dispatch('refreshPermintaanSaya'); //refresh widget setelah approve agar status terupdate
                    }),
            ])
            ->emptyStateHeading('Tidak ada permintaan');
    }
}
