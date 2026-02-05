<?php

namespace App\Filament\Resources\DetailPermintaanResource\Widgets;

use App\Models\DetailPermintaan;
use App\Filament\Resources\DetailPermintaanResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\DetailTerverifikasi;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use App\Models\Permintaan;
use Filament\Tables\Actions\DeleteAction;

class DetailPermintaanTable extends BaseWidget
{
    public $record;
    public bool $canApproval = false; // Atur tombol approval (diatur di detailpermintaanpolicy)
    public bool $canAction = false; // Atur tombol edit dan hapus (diatur di detailpermintaanpolicy)
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Hanya ambil detail milik ID permintaan ini
                DetailPermintaan::query()->where('permintaan_id', $this->record?->id)
            )
            ->header(function () {
                return Infolist::make()
                    ->record($this->record)
                    ->schema([
                        Section::make('Informasi Umum')
                            ->extraAttributes([
                                'class' => '!border-none !shadow-none !bg-transparent !px-0 '
                            ])
                            ->schema([
                                Grid::make(11)
                                    ->schema([
                                        TextEntry::make('id')
                                            ->label('ID')
                                            ->weight('bold')
                                            ->prefix('# '),
                                        TextEntry::make('user.name')
                                            ->label('Peminta')
                                            ->badge()
                                            ->color('gray')
                                            ->columnSpan(4),
                                        TextEntry::make('bagian.nama_bagian')
                                            ->label('Unit Kerja')
                                            ->badge()
                                            ->color('gray')
                                            ->columnSpan(4),
                                        TextEntry::make('created_at')
                                            ->label('Tgl Permintaan')
                                            ->dateTime('d M Y, H:i')
                                            ->badge()
                                            ->color('gray')
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->compact(),
                    ])
                    ->render(); // Penting: tambahkan render() karena ini di dalam header table
            })

            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->description(fn($record) => ($record->barang?->kode_barang ?? '-'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah Minta')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('stok_gudang')
                    ->label('Stok di Gudang')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->gudang?->stok ?? 0),
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
            ->actions([
                Action::make('approve')
                ->visible(fn ($record) =>
                    $this->canApproval
                    && $record->approved === 'pending'
                    && auth()->user()?->can('approve_permintaan')
                )
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Permintaan')

                    ->action(function ($record, $livewire) {
                        $this->authorize('approve', $record);
                        $success = false;
                        DB::transaction(function () use ($record, &$success) {
                            $stokGudang = $record->gudang;
                            if (!$stokGudang || $stokGudang->stok < $record->jumlah) {
                                Notification::make()
                                    ->title('Gagal Approve')
                                    ->body($stokGudang ? 'Stok tidak mencukupi!' : 'Barang tidak terdaftar di gudang bagian ini.')
                                    ->danger()
                                    ->send();
                                return;
                            }

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
                            //set keterangan ke "Pemakaian"
                            $stokGudang->keteranganOtomatis = 'Pemakaian';
                            // Kurangi stok di tabel gudangs
                            $stokGudang->stok -= $record->jumlah;
                            $stokGudang->save();

                            Notification::make()
                                ->title('Permintaan berhasil di-approve')
                                ->success()
                                ->send();

                            $success = true;
                        });

                        if ($success) {
                            $livewire->dispatch('refreshPermintaanSaya');
                        }
                    }),

                Action::make('reject')
                    ->visible(fn ($record) =>
                        $this->canApproval
                        && $record->approved === 'pending'
                        && auth()->user()?->can('approve_permintaan')
                    )                    
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Permintaan')

                    ->action(function ($record, $livewire) {
                        $this->authorize('reject', $record);
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
                        $livewire->dispatch('refreshPermintaanSaya');
                    }),

                Action::make('edit_detail')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->url(fn($record): string => DetailPermintaanResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn($record) => $this->canAction && $record->approved === 'pending'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $this->canAction && $record->approved === 'pending')
                    ->after(function ($record, $livewire) {
                        $permintaanId = $record->permintaan_id;

                        // Cek apakah masih ada detail lain untuk permintaan yang sama
                        $sisaDetail = DetailPermintaan::where('permintaan_id', $permintaanId)->count();

                        if ($sisaDetail === 0) {
                            // Hapus induk (Permintaan) jika sudah kosong
                            Permintaan::find($permintaanId)?->delete();

                            $livewire->dispatch('refreshPermintaanSaya');

                            $livewire->dispatch('close-modal', id: 'view_details');

                            Notification::make()
                                ->title('Seluruh permintaan telah dihapus karena tidak ada item tersisa.')
                                ->info()
                                ->send();
                        } else {
                            $livewire->dispatch('refreshTable');

                            Notification::make()
                                ->title('Item berhasil dihapus.')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->paginated(false);
    }
}
