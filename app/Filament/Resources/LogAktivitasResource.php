<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogAktivitasResource\Pages;
use App\Models\LogAktivitas;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Traits\HasBagianScope;

class LogAktivitasResource extends Resource
{
    use HasBagianScope;
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 5;
    protected static ?string $model = LogAktivitas::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // Hak Akses: Mematikan fungsi Create, Edit, dan Delete
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('clear_log_aktivitas');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('access_log');
    }
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = static::getModel()::query()->with(['user']);

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // 1. Jika Super Admin bisa lihat semua
        if ($user->hasPermissionTo('access_log') && $user->role === 'super_admin') {
            return $query;
        }
    
        return $query->where('user_id', $user->id);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_barang_snapshot')
                    ->label('Item')
                    ->description(fn($record) => "Code: {$record->kode_barang_snapshot}")
                    ->searchable([
                        'nama_barang_snapshot',
                        'kode_barang_snapshot',
                    ]),

                Tables\Columns\TextColumn::make('nama_bagian_snapshot')
                    ->label('Unit / Section')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'inbound' => 'success',
                        'outbound' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Movement')
                    ->weight('bold')
                    ->color(function ($record) {
                        return $record->stok_akhir < $record->stok_awal ? 'danger' : 'success';
                    })
                    ->formatStateUsing(function ($record, $state) {
                        $simbol = $record->stok_akhir < $record->stok_awal ? '-' : '+';
                        return "{$simbol} {$state}";
                    }),

                Tables\Columns\TextColumn::make('stok_awal')
                    ->label('Opening Stock')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stok_akhir')
                    ->label('Closing Stock')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user_snapshot')
                    ->label('By')
                    ->sortable()
                    ->description(fn($record) => ($record->user->email ?? 'Unknown')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'Inbound' => 'Inbound',
                        'Outbound' => 'Outbound',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->visible(fn() => auth()->user()?->hasPermissionTo('access_log'))
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Report Title')
                            ->default('Activity Log Report')
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Start Date')
                            ->default(now()->startOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_akhir')
                            ->label('End Date')
                            ->default(now()->endOfMonth())
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_laporan')
                            ->label('Report Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Increase memory limit for PDF generation
                        ini_set('memory_limit', '1028M');

                        $records = static::getEloquentQuery()
                            ->whereBetween('created_at', [
                                Carbon::parse($data['tanggal_mulai'])->startOfDay(),
                                Carbon::parse($data['tanggal_akhir'])->endOfDay()
                            ])
                            ->orderBy('nama_bagian_snapshot')
                            ->orderBy('created_at', 'desc')
                            ->get();

                        $groupedRecords = $records->groupBy('nama_bagian_snapshot');

                        $periode = Carbon::parse($data['tanggal_mulai'])->locale('en')->translatedFormat('d F Y') . ' - ' . Carbon::parse($data['tanggal_akhir'])->locale('en')->translatedFormat('d F Y');

                        $pdf = Pdf::loadView('pdf.log-aktivitas', [
                            'title' => $data['title'],
                            'tanggal' => Carbon::parse($data['tanggal_laporan'])->locale('en')->translatedFormat('d F Y'),
                            'periode' => $periode,
                            'groupedRecords' => $groupedRecords,
                        ])->setPaper('a4', 'landscape');

                        $response = response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'log-aktivitas-' . now()->format('Y-m-d') . '.pdf');

                        // Free memory after PDF generation
                        unset($pdf, $records, $groupedRecords);
                        gc_collect_cycles();

                        return $response;
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogAktivitas::route('/'),
        ];
    }
}
