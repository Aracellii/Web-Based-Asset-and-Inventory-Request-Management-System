<?php

namespace App\Filament\Widgets;

use App\Models\DetailPermintaan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopRequestedItemsChart extends ChartWidget
{
    protected static ?string $heading = 'Barang Diminta';
    
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '350px';

    public ?string $filter = 'all_time';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->can('keuangan_graphic');
    }

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getFilters(): ?array
    {
        return [
            'all_time' => 'Semua Waktu',
            'this_month' => 'Bulan Ini',
            'last_3_months' => '3 Bulan Terakhir',
            'last_6_months' => '6 Bulan Terakhir',
            'this_year' => 'Tahun Ini',
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Dari Tanggal')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->reactive()
                        ->visible(fn () => $this->filter === 'custom'),
                    DatePicker::make('endDate')
                        ->label('Sampai Tanggal')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->reactive()
                        ->visible(fn () => $this->filter === 'custom'),
                ])
                ->columns(2),
        ];
    }

    protected function getDateRange(): ?array
    {
        return match ($this->filter) {
            'all_time' => null,
            'this_month' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            'last_3_months' => [
                Carbon::now()->subMonths(2)->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            'last_6_months' => [
                Carbon::now()->subMonths(5)->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            'this_year' => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ],
            'custom' => [
                $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null,
                $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null,
            ],
            default => null,
        };
    }

    protected function getData(): array
    {
        $user = Auth::user();
        
        $query = DetailPermintaan::select('barang_id', DB::raw('SUM(jumlah) as total_diminta'))
            ->with('barang');

        // Filter by bagian if not keuangan or super_admin
        if (!$user->isKeuangan() && !$user->isSuperAdmin()) {
            $query->where('bagian_id', $user->bagian_id);
        }

        // Apply date filter
        $dateRange = $this->getDateRange();
        if ($dateRange) {
            [$startDate, $endDate] = $dateRange;
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }
        }

        $topItems = $query
            ->groupBy('barang_id')
            ->orderByDesc('total_diminta')
            ->limit(10)
            ->get();

        $labels = $topItems->map(function ($item) {
            return $item->barang?->nama_barang ?? 'Barang Dihapus';
        })->toArray();

        $data = $topItems->pluck('total_diminta')->toArray();

        // Generate gradient colors
        $backgroundColors = [
            'rgba(245, 158, 11, 0.8)',   // amber
            'rgba(249, 115, 22, 0.8)',   // orange
            'rgba(239, 68, 68, 0.8)',    // red
            'rgba(236, 72, 153, 0.8)',   // pink
            'rgba(168, 85, 247, 0.8)',   // purple
            'rgba(99, 102, 241, 0.8)',   // indigo
            'rgba(59, 130, 246, 0.8)',   // blue
            'rgba(14, 165, 233, 0.8)',   // sky
            'rgba(20, 184, 166, 0.8)',   // teal
            'rgba(34, 197, 94, 0.8)',    // green
        ];

        $borderColors = [
            'rgb(245, 158, 11)',
            'rgb(249, 115, 22)',
            'rgb(239, 68, 68)',
            'rgb(236, 72, 153)',
            'rgb(168, 85, 247)',
            'rgb(99, 102, 241)',
            'rgb(59, 130, 246)',
            'rgb(14, 165, 233)',
            'rgb(20, 184, 166)',
            'rgb(34, 197, 94)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Diminta',
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderColor' => array_slice($borderColors, 0, count($data)),
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // This makes it horizontal
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'autoSkip' => false,
                    ],
                ],
            ],
        ];
    }
}
