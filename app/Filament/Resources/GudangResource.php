<?php
namespace App\Filament\Resources;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\GudangResource\Pages;
use App\Models\Gudang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GudangResource extends Resource
{
    protected static ?string $model = Gudang::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Stok Gudang';
    protected static ?string $modelLabel = 'Stok Barang';
    protected static ?string $pluralModelLabel = 'Stok Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Stok')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->relationship('barang', 'nama_barang')
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_barang')
                                    ->required()
                                    ->unique('barangs', 'nama_barang'),
                                Forms\Components\TextInput::make('harga_satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),])
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('bagian_id')
                            ->relationship('bagian', 'nama_bagian')
                            ->label('Bidang / Bagian')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('stok')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bagian.nama_bagian')
                    ->label('Bidang / Bagian')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Jumlah Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
                ->filters([
                Tables\Filters\SelectFilter::make('bagian_id')
                ->relationship('bagian', 'nama_bagian')
                ->label('Filter per Bidang')             
             ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGudangs::route('/'),
            'create' => Pages\CreateGudang::route('/create'),
            'edit' => Pages\EditGudang::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // Jika yang login bukan admin 
    if (Auth::user()->role !== 'admin') {
        //perbagian
        $query->where('bagian_id', Auth::user()->bagian_id);
    }

    return $query;
}
}