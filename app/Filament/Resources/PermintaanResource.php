<?php

namespace App\Filament\Resources;
use App\Models\DetailPermintaan;
use Illuminate\Support\Facades\DB;
use App\Models\DetailTerverifikasi;
use App\Models\Gudang;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\PermintaanResource\Pages;
use App\Models\Permintaan;
use App\Models\Barang;
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
        if (Auth::user()->role === 'user') {
    
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable(),
                        Forms\Components\DatePicker::make('tanggal_permintaan')
                            ->required()
                            ->default(now()),
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
                                    ->preload()
                                    //Tulis Manual
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_barang')
                                            ->required()
                                            ->unique('barangs', 'nama_barang'),
                                        Forms\Components\TextInput::make('id')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return Barang::create($data)->id;
                                    }),                                   
                                

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

                               
                            ])
                            ->columns(3)
                            ->createItemButtonLabel('Tambah Baris Barang')
                    ])
            ]);
    }
    else{ return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('tanggal_permintaan')
                            ->required()
                            ->default(now()),
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
                                    ->preload()
                                    //Tulis Manual
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_barang')
                                            ->required()
                                            ->unique('barangs', 'nama_barang'),
                                            
                                        Forms\Components\TextInput::make('id')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        return Barang::create($data)->id;
                                    })
                                    // Otomatis isi biaya
                                    ->reactive(),
                                   

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

                              
                            ])
                            ->columns(2)
                            ->createItemButtonLabel('Tambah Baris Barang')
                    ])
            ]);
    }}
    
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $query->where('approved', 'tidak');
    if (Auth::user()->role === 'user') {
        $query->where('user_id', Auth::id());
    }
    return $query;
}

    public static function table(Table $table): Table
    {
        return $table
        
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminta')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_permintaan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detail_permintaans_count')
                    ->label('Jumlah Item')
                    ->counts('detailPermintaans'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),   
               Tables\Columns\TextColumn::make('user.bagian.nama_bagian')
                    ->label('Bidang')
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bagian')
                    ->relationship('user.bagian', 'nama_bagian') 
                    ->label('Filter per Bidang'),
            ])

           ->actions([
            Action::make('approve')
            ->visible(fn () => auth()->user()->role === 'keuangan')
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
                        'detail_id' => $detail->id,
                        'barang_id' => $detail->barang_id,
                        'jumlah'    => $detail->jumlah,
                    ]);
                    $detail->update([
                    'approved' => 'ya',]);

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
                'approved' => 'ya',]);
            });

            Notification::make()
                ->title('Permintaan berhasil di-approve')
                ->success()
                ->send();
        }),
])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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