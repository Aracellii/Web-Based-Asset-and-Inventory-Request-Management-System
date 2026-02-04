<?php

namespace App\Filament\Resources\PermintaanResource\Widgets;

use App\Models\Permintaan;
use App\Models\DetailPermintaan;
use App\Models\DetailTerverifikasi;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\DetailPermintaanResource\Widgets\DetailPermintaanTable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;


class ListPermintaanTable extends BaseWidget
{
    // Widget ini muncul jika user punya permission untuk melihat & approve permintaan
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->hasPermissionTo('access_permintaan') || $user->hasPermissionTo('manage_permintaan');
    }
    protected int | string | array $columnSpan = 'full';
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = Permintaan::query();
        // batasi hanya per bagian (Unit Kerja)
        if (!$user->hasPermissionTo('access_permintaan')) {
            $query->where('bagian_id', $user->bagian_id);
        }

        return $query;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('List Permintaan')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Permintaan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Permintaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('detail_permintaans_count')
                    ->label('Item')
                    ->counts('detailPermintaans')
                    ->badge(),
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
                Tables\Filters\SelectFilter::make('filter_bagian')
                    ->relationship('user.bagian', 'nama_bagian')
                    ->label('Filter Unit Kerja')
                    ->multiple(true)
                    ->preload(),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    // ->infolist([
                    //     Section::make('Informasi Barang')
                    //         ->schema([
                    //             // Baris Header (Hanya muncul sekali di paling atas)
                    //             Grid::make(3)
                    //                 ->schema([
                    //                     TextEntry::make('barang_header')
                    //                         ->default('Nama Barang')
                    //                         ->weight('bold')
                    //                         ->hiddenLabel(),
                    //                     TextEntry::make('qty_header')
                    //                         ->default('Jumlah Minta')
                    //                         ->weight('bold')
                    //                         ->hiddenLabel(),
                    //                     TextEntry::make('stok_header')
                    //                         ->default('Stok Gudang')
                    //                         ->weight('bold')
                    //                         ->hiddenLabel(),
                    //                 ])
                    //                 ->extraAttributes(['class' => 'border-b pb-2 mb-2']), // Tambah garis bawah header

                    //             // Baris Data (Akan looping sebanyak item barang)
                    //             RepeatableEntry::make('detailPermintaans')
                    //                 ->hiddenLabel()
                    //                 ->schema([
                    //                     TextEntry::make('barang.nama_barang')
                    //                         ->hiddenLabel(),
                    //                     TextEntry::make('jumlah')
                    //                         ->badge()
                    //                         ->color('primary')
                    //                         ->hiddenLabel(),
                    //                     TextEntry::make('stok_saat_ini')
                    //                         ->default(0)
                    //                         ->hiddenLabel(),
                    //                 ])
                    //                 ->columns(3) // Harus sama dengan Grid header di atas
                    //         ])
                    // ])
                    ->modalHeading('Detail Permintaan')
                    ->modalContent(fn($record): HtmlString => new HtmlString(
                        Blade::render('@livewire(\App\Filament\Resources\DetailPermintaanResource\Widgets\DetailPermintaanTable::class, ["record" => $record])', [
                            'record' => $record
                        ])
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            // ->actions([
            // Action::make('approve')
            //     ->visible(fn($record) => auth()->user()->hasPermissionTo('update_permintaan') && $record->approved === 'pending')
            //     ->label('Approve')
            //     ->color('success')
            //     ->icon('heroicon-o-check-circle')
            //     ->requiresConfirmation()
            //     ->modalHeading('Approve Permintaan')

            //     ->action(function ($record, $livewire) {
            //         $success = false;
            //         DB::transaction(function () use ($record, &$success) {
            //             $stokGudang = $record->gudang;
            //             if (!$stokGudang || $stokGudang->stok < $record->jumlah) {
            //                 Notification::make()
            //                     ->title('Gagal Approve')
            //                     ->body($stokGudang ? 'Stok tidak mencukupi!' : 'Barang tidak terdaftar di gudang bagian ini.')
            //                     ->danger()
            //                     ->send();
            //                 return;
            //             }

            //             // INSERT detail_terverifikasis
            //             DetailTerverifikasi::create([
            //                 'detail_permintaan_id' => $record->id,
            //                 'bagian_id'    => $record->bagian_id,
            //                 'barang_id' => $record->barang_id,
            //                 'jumlah'    => $record->jumlah,
            //                 'approved'  => 'approved',
            //             ]);
            //             // UPDATE status approved di detail_permintaans
            //             $record->update([
            //                 'approved' => 'approved',
            //             ]);
            //             //set keterangan ke "Pemakaian"
            //             $stokGudang->keteranganOtomatis = 'Pemakaian';
            //             // Kurangi stok di tabel gudangs
            //             $stokGudang->stok -= $record->jumlah;
            //             $stokGudang->save();

            //             Notification::make()
            //                 ->title('Permintaan berhasil di-approve')
            //                 ->success()
            //                 ->send();

            //             $success = true;
            //         });

            //         if ($success) {
            //             $livewire->dispatch('refreshPermintaanSaya');
            //         }
            //     }),

            // Action::make('reject')
            //     ->visible(fn($record) => auth()->user()->hasPermissionTo('update_permintaan') && $record->approved === 'pending')
            //     ->label('Reject')
            //     ->color('danger')
            //     ->icon('heroicon-o-x-circle')
            //     ->requiresConfirmation()
            //     ->modalHeading('Reject Permintaan')

            //     ->action(function ($record, $livewire) {
            //         DB::transaction(function () use ($record) {
            //             // INSERT detail_terverifikasis
            //             DetailTerverifikasi::create([
            //                 'detail_permintaan_id' => $record->id,
            //                 'bagian_id'    => $record->bagian_id,
            //                 'barang_id' => $record->barang_id,
            //                 'jumlah'    => $record->jumlah,
            //                 'approved'  => 'rejected',
            //             ]);

            //             $record->update([
            //                 'approved' => 'rejected',
            //             ]);
            //         });

            //         Notification::make()
            //             ->title('Permintaan berhasil di-reject')
            //             ->success()
            //             ->send();

            //         $livewire->dispatch('refreshPermintaanSaya');
            //     }),
            // ])
            ->emptyStateHeading('Tidak ada permintaan');
    }
}
