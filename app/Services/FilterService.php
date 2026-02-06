<?php
namespace App\Services;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;


class FilterService
{
    public static function dateRangeFilter(string $column = 'created_at'): Filter
    {
        return Filter::make($column)
            ->label('Waktu Aktivitas')
            ->form([
                Select::make('rentang')
                    ->label('Pilih Rentang')
                    ->options([
                        'all' => 'Semua Waktu',
                        '7' => '7 Hari Terakhir',
                        '30' => '30 Hari Terakhir',
                        'this_year' => 'Tahun Ini',
                        'custom' => 'Kustom Tanggal...',
                    ])
                    ->default('all')
                    ->live(),

                Grid::make(2)
                    ->schema([
                        DatePicker::make('dari_tanggal')->label('Dari'),
                        DatePicker::make('sampai_tanggal')->label('Sampai')->default(now()),
                    ])
                    ->visible(fn ($get) => $get('rentang') === 'custom'),
            ])
            ->query(function (Builder $query, array $data) use ($column): Builder {
                return $query
                    ->when($data['rentang'] === 'custom', fn ($q) => $q
                        ->when($data['dari_tanggal'], fn($sq) => $sq->whereDate($column, '>=', $data['dari_tanggal']))
                        ->when($data['sampai_tanggal'], fn($sq) => $sq->whereDate($column, '<=', $data['sampai_tanggal']))
                    )
                    ->when($data['rentang'] === 'this_year', fn ($q) => $q->whereYear($column, now()->year))
                    ->when(in_array($data['rentang'], ['7', '30']), fn ($q) => $q->where($column, '>=', now()->subDays((int) $data['rentang'])));
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                if ($data['rentang'] === 'custom' && ($data['dari_tanggal'] || $data['sampai_tanggal'])) {
                    $indicators[] = 'Periode: ' . ($data['dari_tanggal'] ?? '...') . ' - ' . ($data['sampai_tanggal'] ?? '...');
                } elseif ($data['rentang'] !== 'all' && filled($data['rentang'])) {
                    $indicators[] = 'Rentang: ' . $data['rentang'];
                }
                return $indicators;
            });
    }
}