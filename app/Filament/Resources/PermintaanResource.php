<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanResource\Pages;
use App\Models\Permintaan;
use App\Models\Gudang;
use App\Models\User;
use App\Models\DetailPermintaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasBagianScope;
use Filament\Tables\Actions\Action;
use Filament\Infolists\Components\Livewire;
use App\Filament\Resources\DetailPermintaanResource\Widgets\DetailPermintaanTable;
use App\Services\FilterService;

class PermintaanResource extends Resource
{
    use HasBagianScope;
    protected static ?string $navigationGroup = 'Gudang';
    protected static ?int $navigationSort = 4;
    protected static ?string $model = Permintaan::class;
    protected static ?string $modelLabel = 'Permintaan';

    protected static ?string $pluralModelLabel = 'Permintaan';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('akses_permintaan');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('akses_permintaan');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_permintaan');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_permintaan');
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
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('created_at')
                                    ->label('Tanggal Permintaan')
                                    ->default(now())
                                    ->disabled(),
                                Forms\Components\TimePicker::make('created_at_time')
                                    ->label(new \Illuminate\Support\HtmlString('&nbsp;')) // Memaksa label ada tapi kosong
                                    ->default(now())
                                    ->disabled(),
                            ])
                            ->columnSpan(1),
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
                                    ->rules([
                                        fn($get): \Closure =>
                                        function ($attribute, $value, \Closure $fail) use ($get) {
                                            $selectedBarang = collect($get('../../detailPermintaans'))
                                                ->pluck('barang_id')
                                                ->filter();

                                            $counts = $selectedBarang->countBy();

                                            if ($counts->get($value) > 1) {
                                                $fail('Barang ini sudah dipilih di baris lain.');
                                            }
                                        },

                                    ])
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $stokGudang = Gudang::where('barang_id', $state)->value('stok');
                                        $set('stok_saat_ini', $stokGudang ?? 0);
                                    }),
                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah Minta')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Qty:')
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->minValue(1)
                                    // Validasi tidak boleh lebih dari stok yang ada di gudang
                                    ->maxValue(fn($get) => (int) $get('stok_saat_ini')),
                                Forms\Components\Hidden::make('bagian_id')
                                    ->default(function (callable $get) {
                                        // Ambil user_id dari komponen di luar repeater
                                        $userId = $get('../../user_id');
                                        if ($userId) {
                                            return User::find($userId)?->bagian_id;
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
                                    ->placeholder('-')

                                    // Load stok awal jika sedang dalam mode Edit
                                    ->afterStateHydrated(function ($state, $set, $get) {
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
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Permintaan')
                    ->sortable()
                    ->weight('bold')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('detailPermintaans.barang.nama_barang')
                    ->label('Preview Barang')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->color('gray')
                    ->size('sm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Permintaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->searchable(),

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
                    ->color(function ($state) {
                        [$processed, $total] = explode(' / ', $state);
                        if ($processed == 0) return 'gray';
                        if ($processed == $total) return 'success';
                        return 'warning';
                    })
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
                                'canAction' => 'true', //bisa edit dan hapus
                                'canApproval' => false, //tidak bisa approve/reject
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

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        return $query->where('user_id', $user->id);
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
        ];
    }
}
