<?php

namespace App\Filament\Resources\PermintaanResource\Widgets;

use App\Models\Permintaan;
use App\Models\DetailTerverifikasi;
use App\Models\Gudang;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


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
        $query = Permintaan::query();
        $query->where('approved', 'pending');

        // Filter berdasarkan role admin dan bagian yang sesuai
        if ($user->role === 'admin') {
            $query->whereHas('user', function ($q) use ($user) {
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_permintaan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.bagian.nama_bagian')
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
            ->filters([
                Tables\Filters\SelectFilter::make('bagian')
                    ->relationship('user.bagian', 'nama_bagian')
                    ->label('Filter per Bidang'),
            ])
            ->actions([
                Action::make('approve')
                    ->visible(fn() => auth()->user()->role === 'admin')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Permintaan')
                    ->action(function ($record) {

                        DB::transaction(function () use ($record) {
                            $record->load('detailPermintaans', 'user');

                            if ($record->detailPermintaans->isEmpty()) {
                                throw new \Exception('Detail permintaan kosong');
                            }

                            foreach ($record->detailPermintaans as $detail) {
                                // INSERT detail_terverifikasis
                                DetailTerverifikasi::create([
                                    'permintaan_id' => $detail->permintaan_id,
                                    'bagian_id'    => $detail->bagian_id,
                                    'barang_id' => $detail->barang_id,
                                    'jumlah'    => $detail->jumlah,
                                ]);
                                $detail->update([
                                    'approved' => 'approved',
                                ]);

                                // Update stok gudang
                                $stokGudang = Gudang::firstOrCreate(
                                    [
                                        'barang_id' => $detail->barang_id,
                                        'bagian_id' => $record->user->bagian_id,
                                    ],
                                    ['stok' => 0]
                                );
                                $stokGudang->increment('stok', $detail->jumlah);
                            }
                            $record->update([
                                'approved' => 'approved',
                            ]);
                        });

                        Notification::make()
                            ->title('Permintaan berhasil di-approve')
                            ->success()
                            ->send();

                        return redirect(request()->header('Referer'));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->successRedirectUrl(fn() => static::getUrl('index'));
    }
}
