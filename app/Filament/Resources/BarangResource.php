<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Traits\HasBagianScope;

class BarangResource extends Resource
{
    use HasBagianScope;
    
    protected static ?string $model = Barang::class;
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Item Catalog';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Item';
    protected static ?string $pluralModelLabel = 'Item Catalog';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('akses_katalog') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('manage_katalog_barang') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_katalog_barang') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('manage_katalog_barang') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Item Information')
                    ->description('Add a new item to the catalog')
                    ->schema([
                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Item Code')
                            ->placeholder('Enter item code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Item Name')
                            ->placeholder('Enter item name')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Ambil semua bagian untuk dijadikan kolom dinamis
        $bagians = Bagian::all();

        $columns = [
            Tables\Columns\TextColumn::make('index')
                ->label('No')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('kode_barang')
                ->label('Item Code')
                ->sortable()
                ->searchable()
                ->copyable()
                ->weight('bold'),
            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Item Name')
                ->sortable()
                ->searchable()
                ->wrap(),
        ];

        // Tambahkan kolom total stok
        $columns[] = Tables\Columns\TextColumn::make('total_stok')
            ->label('Total Stock')
            ->getStateUsing(function (Barang $record) {
                return Gudang::where('barang_id', $record->id)->sum('stok');
            })
            ->badge()
            ->color('primary')
            ->sortable(query: function (Builder $query, string $direction): Builder {
                return $query->orderBy(
                    Gudang::selectRaw('COALESCE(SUM(stok), 0)')
                        ->whereColumn('gudangs.barang_id', 'barangs.id'),
                    $direction
                );
            })
            ->alignCenter()
            ->weight('bold');

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label('Created At')
            ->dateTime('d M Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = Tables\Columns\TextColumn::make('updated_at')
            ->label('Last Updated')
            ->dateTime('d M Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->columns($columns)
            ->defaultSort('nama_barang', 'asc')
            
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Item')
                    ->modalDescription('Are you sure you want to delete this item? It will be removed permanently.')
                    ->modalSubmitActionLabel('Yes, Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->emptyStateHeading('No items yet')
            ->emptyStateDescription('')
            ->emptyStateIcon('heroicon-o-cube');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Item Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('kode_barang')
                            ->label('Item Code')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('nama_barang')
                            ->label('Item Name'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y H:i'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Stock by Unit')
                    ->schema(function (Barang $record): array {
                        $entries = [];
                        $gudangs = Gudang::where('barang_id', $record->id)
                            ->with('bagian')
                            ->get();
                        
                        foreach ($gudangs as $gudang) {
                            $entries[] = Infolists\Components\TextEntry::make('stok_' . $gudang->bagian_id)
                                ->label($gudang->bagian->nama_bagian ?? 'Unknown')
                                ->state($gudang->stok)
                                ->badge()
                                ->color(fn(int $state): string => match (true) {
                                    $state <= 5 => 'danger',
                                    $state <= 20 => 'warning',
                                    default => 'success',
                                });
                        }

                        $entries[] = Infolists\Components\TextEntry::make('total_stok')
                            ->label('Grand Total')
                            ->state(Gudang::where('barang_id', $record->id)->sum('stok'))
                            ->badge()
                            ->color('primary')
                            ->weight('bold');

                        return $entries;
                    })->columns(4),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'view' => Pages\ViewBarang::route('/{record}'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string{
            $count = Barang::whereIn('id', function ($query) {
                $query->select('barang_id')
                    ->from('gudangs')
                    ->where('stok', 0);
            })->count();    
    
            return $count > 0 ? (string)$count : null; }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        // If the user has catalog access, return all items
        if ($user && $user->hasPermissionTo('akses_katalog')) {
            return $query;
        }
        
        // Otherwise, show only items in the user's warehouse unit
        if ($user && $user->hasPermissionTo('akses_katalog') && $user->bagian_id) {
            return $query->whereHas('gudangs', function ($q) use ($user) {
                $q->where('bagian_id', $user->bagian_id);
            });
        }
        
        // No access
        return $query->whereRaw('1 = 0');
    }
}
