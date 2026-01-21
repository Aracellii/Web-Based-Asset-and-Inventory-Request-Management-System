<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanResource\Pages;
use App\Models\Permintaan;
use App\Models\Barang;
use App\Models\DetailPermintaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;

class PermintaanResource extends Resource
{
    protected static ?string $model = Permintaan::class;
    protected static ?string $modelLabel = 'Permintaan';

    protected static ?string $pluralModelLabel = 'Permintaan';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\DatePicker::make('tanggal_permintaan')
                            ->required()
                            ->default(now())
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Barang')
                    ->schema([
                        Forms\Components\Repeater::make('detailPermintaans')
                            ->relationship() // Menghubungkan ke tabel detail_permintaans
                            ->schema([
                                Forms\Components\Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship('barang', 'nama_barang')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    // Hitung 
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $barangId = $get('barang_id');
                                        $barang = Barang::find($barangId);
                                        if ($barang) {
                                            $set('biaya', $state * $barang->harga_satuan);
                                        }
                                    }),
                                Forms\Components\Hidden::make('bagian_id')
                                    ->default(function (callable $get) {
                                        // Ambil user_id dari komponen di luar repeater
                                        $userId = $get('../../user_id');
                                        if ($userId) {
                                            // Cari user tersebut dan ambil bagian_id-nya
                                            return \App\Models\User::find($userId)?->bagian_id;
                                        }
                                        // Fallback ke user yang sedang login jika belum pilih user
                                        return auth()->user()->bagian_id;
                                    })
                                    ->dehydrated(true),


                            ])
                            ->columns(3)
                            ->createItemButtonLabel('Tambah Baris Barang')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Permintaan Saya')
            ->query(
                DetailPermintaan::query()->whereHas('permintaan', function ($q) {
                    $q->where('user_id', Auth::id());
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('permintaan.user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('permintaan.tanggal_permintaan')
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
                Tables\Columns\TextColumn::make('permintaan.created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->filters([
                Tables\Filters\SelectFilter::make('filter_bagian')
                    ->relationship('permintaan.user.bagian', 'nama_bagian')
                    ->label('Filter per Bidang'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada permintaan');;
    }



    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermintaans::route('/'),
            'create' => Pages\CreatePermintaan::route('/create'),
            'edit' => Pages\EditPermintaan::route('/{record}/edit'),
        ];
    }
}
