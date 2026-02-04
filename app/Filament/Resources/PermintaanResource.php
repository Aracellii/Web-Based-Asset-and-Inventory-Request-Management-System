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
use App\Traits\HasBagianScope;

class PermintaanResource extends Resource
{
    use HasBagianScope;
    protected static ?int $navigationSort = 4;
    protected static ?string $model = Permintaan::class;
    protected static ?string $modelLabel = 'Permintaan';

    protected static ?string $pluralModelLabel = 'Permintaan';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('access_permintaan');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_permintaan');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('manage_permintaan');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('manage_permintaan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Peminta')
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Permintaan')
                            ->default(now())
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Daftar Barang')
                    ->schema([
                        Forms\Components\Repeater::make('detailPermintaans')
                            ->label('Detail Permintaan')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('barang_id')
                                    ->label('Barang')
                                    ->relationship('barang', 'nama_barang')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Ambil stok langsung dari tabel GUDANG
                                        $stokGudang = \App\Models\Gudang::where('barang_id', $state)->value('stok');
                                        $set('stok_saat_ini', $stokGudang ?? 0);
                                    }),
                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah Minta')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->minValue(1)
                                    // Validasi keras: tidak boleh lebih dari stok yang ada di gudang
                                    ->maxValue(fn($get) => (int) $get('stok_saat_ini')),
                                Forms\Components\Hidden::make('bagian_id')
                                    ->default(function (callable $get) {
                                        // Ambil user_id dari komponen di luar repeater
                                        $userId = $get('../../user_id');
                                        if ($userId) {
                                            return \App\Models\User::find($userId)?->bagian_id;
                                        }
                                        return auth()->user()->bagian_id;
                                    })
                                    ->dehydrated(true),
                                Forms\Components\TextInput::make('stok_saat_ini')
                                    ->label('Stok Saat Ini')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('Qty:')
                                    ->helperText('Sisa stok yang tersedia saat ini.')
                                    ->placeholder('0')
                                    // Load stok awal jika sedang dalam mode Edit
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        // Pastikan saat Edit, stok gudang tetap terisi
                                        $barangId = $get('barang_id');
                                        if ($barangId) {
                                            $stok = Gudang::where('barang_id', $barangId)->value('stok');
                                            $set('stok_saat_ini', $stok ?? 0);
                                        }
                                    }),
                            ])
                            ->columns(3)
                            ->addable(function ($livewire) {
                                if ($livewire instanceof \Filament\Resources\Pages\EditRecord) {
                                    return false;
                                }
                                return true;
                            })
                            ->addActionLabel('Tambah Baris Barang')
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
                Tables\Columns\TextColumn::make('permintaan.created_at')
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
                    ->label('Unit Kerja')
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
            ->recordUrl(null)
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
                        if ($data['rentang'] === 'this_year') {
                            return $query->whereYear('created_at', Carbon::now()->year);
                        }
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
                    ->label('Filter Unit Kerja')
                    ->multiple(true)
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(
                        fn(DetailPermintaan $record): string =>
                        DetailPermintaanResource::getUrl('edit', ['record' => $record->id])
                    )
                    ->visible(fn(DetailPermintaan $record): bool => $record->approved === 'pending'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(DetailPermintaan $record): bool => $record->approved === 'pending')
                    ->action(function (DetailPermintaan $record) {
                        $permintaan = $record->permintaan;
                        if ($permintaan->detailPermintaans()->count() == 1) {
                            $permintaan->delete();
                        } else {
                            $record->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->approved === 'pending') {
                                    $permintaan = $record->permintaan;
                                    if ($permintaan->detailPermintaans()->count() == 1) {
                                        $permintaan->delete();
                                    } else {
                                        $record->delete();
                                    }
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Tidak ada permintaan');;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Apply user scope berdasarkan permission (via user's bagian)
        return static::applyUserScope($query, 'user_id');
    }


    public static function getRelations(): array
    {
        return [];
    }
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return null;
        }

        $count = DetailPermintaan::where('approved', 'pending')
            ->when($user->isAdmin(), function ($query) use ($user) {
                // Admin lihat pending
                return $query->whereHas('permintaan.user', function ($q) use ($user) {
                    $q->where('users.bagian_id', $user->bagian_id);
                });
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
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
