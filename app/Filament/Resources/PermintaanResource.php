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
use Carbon\Carbon;
use Filament\Forms\Components\Select;

class PermintaanResource extends Resource
{
    protected static ?int $navigationSort = 4;
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
                Tables\Columns\TextColumn::make('permintaan_id')
                    ->label('ID Permintaan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('permintaan.tanggal_permintaan')
                    ->date()
                    ->sortable()
                    ->searchable()
                    ->label('Tgl Permintaan'),
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
                    ->label('Filter Status')
                    ->multiple(true),
                Tables\Filters\SelectFilter::make('filter_bagian')
                    ->relationship('permintaan.user.bagian', 'nama_bagian')
                    ->label('Filter Bidang')
                    ->multiple(true)
                    ->preload(),
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
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if($user->role != 'admin') {
            return null;
        }

        // Hitung detail permintaan yang statusnya pending
        $count = DetailPermintaan::where('approved', 'pending')
            ->when($user->role === 'admin', function ($query) use ($user) {
                // Admin hanya melihat hitungan pending dari bagiannya sendiri
                return $query->whereHas('permintaan.user', function ($q) use ($user) {
                    $q->where('users.bagian_id', $user->bagian_id);
                });
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Kuning untuk pending
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
