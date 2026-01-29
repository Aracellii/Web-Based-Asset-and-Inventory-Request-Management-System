<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GudangResource\Pages;
use App\Models\Gudang;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class GudangResource extends Resource
{
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Gudang::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Stok Barang';
    protected static ?string $modelLabel = 'Stok Barang';
    protected static ?string $pluralModelLabel = 'Stok Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Input Stok Gudang')
                    ->disabled(fn($context) => $context === 'edit' && auth()->user()?->role === 'user')
                    ->description('Pilih barang dan tentukan stok')
                    ->schema([
                        Forms\Components\Select::make('barang_id')
                            ->label('Nama Barang')
                            ->relationship('barang', 'nama_barang')
                            ->searchable()
                            ->preload()
                            ->disabled(fn($context) => $context === 'edit')
                            ->required(),
                        Forms\Components\TextInput::make('stok')
                            ->label('Jumlah Stok Terbaru')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Forms\Components\Select::make('bagian_id')
                            ->label('Bagian')
                            ->relationship('bagian', 'nama_bagian')
                            ->searchable()
                            ->preload()
                            ->disabled(fn($context) => $context === 'edit')
                            ->visible(fn($context) => $context === 'edit' || (auth()->user()?->role !== 'keuangan'))
                            ->required(fn($context) => $context === 'edit' || (auth()->user()?->role !== 'keuangan')),

                        Forms\Components\Select::make('bagian_ids')
                            ->label('Pilih Bagian')
                            ->multiple()
                            ->options(\App\Models\Bagian::pluck('nama_bagian', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn($context) => $context === 'create' && auth()->user()?->role === 'keuangan')
                            ->required(fn($context) => $context === 'create' && auth()->user()?->role === 'keuangan')
                            ->helperText('Pilih satu atau lebih bagian untuk menambahkan stok'),

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),
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
                // 1. ACTION EXCEL 
                Tables\Actions\Action::make('export_excel')
                    ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                    ->label('Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_laporan')
                            ->label('Pilih Tanggal Laporan')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('custom_title')
                            ->label('Judul Laporan')
                            ->default('Laporan Stok Barang Gudang'),
                    ])
                    ->action(function (Tables\Table $table, array $data) {
                        // Data yang sudah difilter di tabel
                        $records = $table->getLivewire()->getFilteredTableQuery()->with(['barang', 'bagian'])->get();

                        return response()->streamDownload(function () use ($records, $data) {
                            $grouped = $records->groupBy(fn($item) => $item->bagian->nama_bagian ?? 'Tanpa Bagian');

                            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                            $first = true;

                            foreach ($grouped as $bagianName => $items) {
                                if ($first) {
                                    $sheet = $spreadsheet->getActiveSheet();
                                    $first = false;
                                } else {
                                    $sheet = $spreadsheet->createSheet();
                                }

                                $title = substr($bagianName, 0, 31);
                                try {
                                    $sheet->setTitle($title);
                                } catch (\Exception $e) {
                                    $sheet->setTitle(mb_substr($title, 0, 31));
                                }

                                // Judul & Header Laporan per sheet
                                $sheet->setCellValue('A1', strtoupper($data['custom_title']));
                                $sheet->setCellValue('A2', 'TANGGAL LAPORAN: ' . Carbon::parse($data['tanggal_laporan'])->translatedFormat('d F Y'));
                                $sheet->setCellValue('A3', 'BAGIAN: ' . $bagianName);
                                $sheet->mergeCells('A1:D1');
                                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                                // Header Tabel 
                                $headers = ['Nama Barang', 'Kode Barang', 'Jumlah Stok'];
                                $sheet->fromArray(array_merge(['Lokasi Bagian'], $headers), null, 'A5');
                                $sheet->getStyle('A5:D5')->getFont()->setBold(true);

                                // Isi Data 
                                $row = 6;
                                foreach ($items as $item) {
                                    $sheet->setCellValue('A' . $row, $item->bagian->nama_bagian ?? '-');
                                    $sheet->setCellValue('B' . $row, $item->barang->nama_barang ?? '-');
                                    $sheet->setCellValue('C' . $row, $item->barang->kode_barang ?? '-');
                                    $sheet->setCellValue('D' . $row, $item->stok);
                                    $row++;
                                }

                                // Auto-width agar kolom tidak terpotong
                                foreach (range('A', 'D') as $col) {
                                    $sheet->getColumnDimension($col)->setAutoSize(true);
                                }
                            }

                            $spreadsheet->setActiveSheetIndex(0);

                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                            $writer->save('php://output');
                        }, 'stok-barang-' . $data['tanggal_laporan'] . '.xlsx');
                    }),

                // 2. ACTION PDF )
                Tables\Actions\Action::make('export_pdf')
                    ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_laporan')
                            ->label('Pilih Tanggal Laporan')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('custom_title')
                            ->label('Judul Laporan')
                            ->default('Laporan Stok Barang Gudang'),
                    ])
                    ->action(function (Tables\Table $table, array $data) {
                        $records = $table->getLivewire()
                            ->getFilteredTableQuery()
                            ->with(['barang', 'bagian'])
                            ->get()
                            ->groupBy(fn($item) => $item->bagian->nama_bagian ?? 'Tanpa Bagian');

                        $pdf = Pdf::loadView('pdf.stok-barang', [
                            'groupedRecords' => $records,
                            'title'          => $data['custom_title'],
                            'tanggal'        => $data['tanggal_laporan'],
                        ]);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'stok-barang-' . $data['tanggal_laporan'] . '.pdf'
                        );
                    }),
            ]) // Tutup headerActions
            ->filters([
                Tables\Filters\SelectFilter::make('bagian_id')
                    ->relationship('bagian', 'nama_bagian')
                    ->label('Filter per Bidang')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin'])),
                Tables\Actions\DeleteAction::make()
                    ->label('Kosongkan')
                    ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                    ->modalHeading('Reset stok gudang?')
                    ->modalDescription('Stok akan dikosongkan')
                    ->modalSubmitActionLabel('Reset stok')
                    ->successNotificationTitle('Stok berhasil di reset')
                    ->using(function (Gudang $record): bool {
                        return $record->update(['stok' => 0]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                        ->modalHeading('Reset stok gudang yang dipilih?')
                        ->modalDescription('Stok akan di reset')
                        ->modalSubmitActionLabel('Reset stok')
                        ->successNotificationTitle('Stok terpilih berhasil di reset')
                        ->using(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each(function (Gudang $record): void {
                                $record->update(['stok' => 0]);
                            });
                        }),
                ])
                    ->label('Kosongkan Stok Terpilih')
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        //Tampilkan Data Gudang
        $query = parent::getEloquentQuery();
        // Filter hanya tampilkan gudang yang barangnya belum dihapus
        $query->whereHas('barang');
        // Filter Role
        if (Auth::user()->role !== 'keuangan') {
            $query->where('bagian_id', Auth::user()->bagian_id);
        }

        return $query;
    }
    public static function getNavigationBadge(): ?string
    {
        $count = \App\Models\Barang::whereIn('id', function ($query) {
            $query->select('barang_id')
                ->from('gudangs')
                ->where('stok', 0);
        })->count();

        return $count > 0 ? (string)$count : null;
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGudangs::route('/'),
            'create' => Pages\CreateGudang::route('/create'),
            'edit' => Pages\EditGudang::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['keuangan']);
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return in_array(auth()->user()?->role, ['keuangan', 'admin']);
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return in_array(auth()->user()?->role, ['keuangan', 'admin']);
    }
}
