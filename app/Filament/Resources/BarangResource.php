<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Data Barang';

    protected static ?string $modelLabel = 'Barang';

    protected static ?string $pluralModelLabel = 'Data Barang';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Barang')
                    ->description('Masukkan data barang')
                    ->schema([
                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->placeholder('Masukkan Kode Barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->placeholder('Masukkan Nama Barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_stok')
                    ->label('Total Stok (Semua Bidang)')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withSum('gudangs', 'stok')
                            ->orderBy('gudangs_sum_stok', $direction);
                    })
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_pdf')
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan']))
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_laporan')
                            ->label('Pilih Tanggal Laporan')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('custom_title')
                            ->label('Judul Laporan')
                            ->default('Laporan Data Barang'),
                    ])
                    ->action(function (Table $table, array $data) {
                        $records = $table->getLivewire()
                            ->getFilteredTableQuery()
                            ->with('gudangs.bagian')
                            ->get();

                        $pdf = Pdf::loadView('pdf.data-barang', [
                            'records' => $records,
                            'title'   => $data['custom_title'],
                            'tanggal' => $data['tanggal_laporan'],
                        ]);
                        
                        $filename = 'data-barang-' . $data['tanggal_laporan'] . '.pdf';
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename
                        );
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('stok_rendah')
                    ->label('Stok Rendah (< 5)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('gudangs', function ($q) {
                            $q->select('barang_id')
                              ->groupBy('barang_id')
                              ->havingRaw('SUM(stok) <= 5');
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (Barang $record) => view('filament.resources.barang-resource.detail-stok', [
                        'barang' => $record,
                        'stokPerBidang' => Gudang::where('barang_id', $record->id)
                            ->with('bagian')
                            ->get(),
                    ])),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan']))
                    ->before(function (Barang $record) {
                        // Hapus semua data gudang terkait barang ini
                        Gudang::where('barang_id', $record->id)->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => in_array(auth()->user()?->role, ['keuangan']))
                        ->before(function (\Illuminate\Database\Eloquent\Collection $records) {
                            // Hapus semua data gudang terkait barang yang dipilih
                            Gudang::whereIn('barang_id', $records->pluck('id'))->delete();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StokGudangRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'keuangan';
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->role === 'keuangan';
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->role === 'keuangan';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'keuangan';
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->role === 'keuangan';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'keuangan';
    }
}
