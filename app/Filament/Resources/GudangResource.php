<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GudangResource\Pages;
use App\Models\Gudang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

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
            Forms\Components\Section::make('Input Stok Gudang')
                ->disabled(fn ($context) => $context === 'edit' && auth()->user()?->role === 'user')
                ->description('Pilih barang dan tentukan stok')
                ->schema([    
                    Forms\Components\Select::make('barang_id')
                        ->label('Nama Barang')
                        ->relationship('barang', 'nama_barang')
                        ->searchable()
                        ->preload()
                        ->disabled(fn ($context) => $context === 'edit' && auth()->user()?->role === 'admin','user')
                        ->required()
                        ->editOptionForm([ 
                            Forms\Components\TextInput::make('nama_barang')
                                ->required(),
                        ])
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nama_barang')
                                ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                                ->label('Nama Barang Baru')
                                ->placeholder('Contoh: Kertas A4')
                                ->required()
                                ->unique('barangs', 'nama_barang'),
                                Forms\Components\TextInput::make('id')
                                ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                                ->label('Kode Barang')
                                ->placeholder('Masukkan Kode Barang')
                                ->required()
                                ->unique('barangs', 'id'),
                        ])
                        ->createOptionUsing(function (array $data) {
                            return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                                $barang = \App\Models\Barang::create([
                                    'id' => $data['id'],
                                    'nama_barang' => $data['nama_barang'],
                                ]);
                                $bagians = \App\Models\Bagian::all();

                                foreach ($bagians as $bagian) {
                                    \App\Models\Gudang::create([
                                        'barang_id' => $data['id'], 
                                        'bagian_id' => $bagian->id,
                                        'stok'      => 0,
                                    ]);
                                }
                                \Filament\Notifications\Notification::make()
                                    ->title('Barang Berhasil Dibuat')
                                    ->success()
                                    ->send();

                                return $data['id']; 
                            });
                        }),
                   
                    Forms\Components\TextInput::make('stok')
                        ->label('Jumlah Stok Sekarang')
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Jumlah Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([

                Tables\Actions\Action::make('export_pdf')
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    //Form untuk tanggal
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_laporan')
                            ->label('Pilih Tanggal Laporan')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('custom_title')
                            ->label('Judul Laporan')
                            ->default('Laporan Stok Barang Gudang'),
                    ])

                  ->action(function (Table $table, array $data) {

                    $records = $table->getLivewire()
                        ->getFilteredTableQuery()
                        ->with(['barang', 'bagian'])
                        ->get()
                        ->groupBy(fn ($item) => $item->bagian->nama_bagian ?? 'Tanpa Bagian');

                    $pdf = Pdf::loadView('pdf.stok-barang', [
                        'groupedRecords' => $records,
                        'title'          => $data['custom_title'],
                        'tanggal'        => $data['tanggal_laporan'],
                    ]);
                    $filename = 'stok-barang-' . $data['tanggal_laporan'] . '.pdf';
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $filename
                    );
                })

                    
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bagian_id')
                    ->relationship('bagian', 'nama_bagian')
                    ->label('Filter per Bidang'),
                    
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => in_array(auth()->user()?->role, ['keuangan', 'admin'])),
                ]),
                
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        //Tampilkan Data Gudang
        $query = parent::getEloquentQuery();

        // Filter Role
        if (Auth::user()->role !== 'keuangan') {
            $query->where('bagian_id', Auth::user()->bagian_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGudangs::route('/'),
            'create' => Pages\CreateGudang::route('/create'),
            'edit' => Pages\EditGudang::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['keuangan']);
    }
}