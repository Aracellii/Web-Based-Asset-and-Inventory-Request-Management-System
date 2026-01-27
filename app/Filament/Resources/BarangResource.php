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
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Katalog Barang';
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Katalog Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Barang')
                    ->description('Tambahkan barang baru ke dalam katalog')
                    ->schema([
                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->placeholder('Masukkan kode barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->placeholder('Masukkan nama barang')
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
                ->label('Kode Barang')
                ->sortable()
                ->searchable()
                ->copyable()
                ->weight('bold'),
            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Nama Barang')
                ->sortable()
                ->searchable()
                ->wrap(),
        ];

        // Tambahkan kolom total stok
        $columns[] = Tables\Columns\TextColumn::make('total_stok')
            ->label('Total Stok')
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
            ->label('Tanggal Dibuat')
            ->dateTime('d M Y')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = Tables\Columns\TextColumn::make('updated_at')
            ->label('Terakhir Diubah')
            ->dateTime('d M Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->columns($columns)
            ->defaultSort('nama_barang', 'asc')
            
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Barang')
                    ->modalDescription('Apakah Anda yakin ingin menghapus barang ini? Barang Ini akan dihapus secara permanen')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada barang')
            ->emptyStateDescription('')
            ->emptyStateIcon('heroicon-o-cube');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Barang')
                    ->schema([
                        Infolists\Components\TextEntry::make('kode_barang')
                            ->label('Kode Barang')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('nama_barang')
                            ->label('Nama Barang'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime('d M Y H:i'),
                    ])->columns(3),
                
                Infolists\Components\Section::make('Stok Per Bidang')
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
                            ->label('Total Keseluruhan')
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
            $count = \App\Models\Barang::whereIn('id', function ($query) {
                $query->select('barang_id')
                    ->from('gudangs')
                    ->where('stok', 0);
            })->count();    
    
            return $count > 0 ? (string)$count : null; }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['keuangan']);
}

}
