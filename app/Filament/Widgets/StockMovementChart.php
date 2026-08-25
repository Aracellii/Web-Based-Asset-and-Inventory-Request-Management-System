<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Gudang;
use App\Models\LogAktivitas;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StockMovementChart extends ChartWidget
{
    protected static ?string $heading = 'Stock Movement';
    
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->can('chart_stock');
    }

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'All Items'];
        
        $user = Auth::user();
        
        // Show only items in the user's warehouse unit
        if (!$user->isFinance() && !$user->isSuperAdmin()) {
            $barangIds = Gudang::where('bagian_id', $user->bagian_id)
                ->pluck('barang_id')
                ->toArray();
            
            $barangs = Barang::whereIn('id', $barangIds)
                ->orderBy('nama_barang')
                ->get();
        } else {
            $barangs = Barang::orderBy('nama_barang')->get();
        }
        
        foreach ($barangs as $barang) {
            $filters[(string) $barang->id] = $barang->nama_barang;
        }
        
        return $filters;
    }

    protected function getData(): array
    {
        $months = collect();
        $masukData = collect();
        $keluarData = collect();
        $stokBulananData = collect();

        // Use the last 12 months
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Build the month sequence
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        // Item filter
        $barangId = ($this->filter !== 'all' && $this->filter !== null) ? (int) $this->filter : null;

        // Limit to the user's work unit
        $user = Auth::user();
        $gudangIds = null;
        $bagianId = null;
        
        // Apply the user's warehouse filter
        if (!$user->isFinance() && !$user->isSuperAdmin()) {
            $bagianId = $user->bagian_id;
            $gudangIds = Gudang::where('bagian_id', $bagianId)
                ->pluck('id')
                ->toArray();
        }

        // Current stock from the warehouse table
        $stokSekarangQuery = Gudang::query();
        if ($bagianId !== null) {
            $stokSekarangQuery->where('bagian_id', $bagianId);
        }
        if ($barangId !== null) {
            $stokSekarangQuery->where('barang_id', $barangId);
        }
        $stokSekarang = $stokSekarangQuery->sum('stok');

        // Collect monthly data
        $monthsData = collect();
        while ($current <= $end) {
            $monthLabel = $current->translatedFormat('M Y');
            
            // Current month query
            $masukQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '>', 'stok_awal');

            $keluarQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '<', 'stok_awal');

            // Filter by warehouse
            if ($gudangIds !== null) {
                $masukQuery->whereIn('gudang_id', $gudangIds);
                $keluarQuery->whereIn('gudang_id', $gudangIds);
            }

            // Filter by item
            if ($barangId !== null) {
                $masukQuery->where('barang_id', $barangId);
                $keluarQuery->where('barang_id', $barangId);
            }

            // Inbound items = closing stock greater than opening stock
            $masuk = $masukQuery->sum('jumlah');

            // Outbound items = closing stock lower than opening stock
            $keluar = $keluarQuery->sum('jumlah');

            $monthsData->push([
                'label' => $monthLabel,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'isCurrent' => $current->isSameMonth(Carbon::now()),
            ]);

            $current->addMonth();
        }

        // Reverse-calculate previous stock from the current stock
        $stokKumulatif = $stokSekarang;
        $reversedMonths = $monthsData->reverse()->values();
        $stokPerBulan = collect();

        foreach ($reversedMonths as $index => $data) {
            if ($index === 0) {
                // Latest month (current)
                $stokPerBulan->prepend($stokKumulatif);
            } else {
                // Previous month: stock = next month stock - next month inbound + next month outbound
                $prevData = $reversedMonths[$index - 1];
                $stokKumulatif = $stokKumulatif - $prevData['masuk'] + $prevData['keluar'];
                $stokPerBulan->prepend($stokKumulatif);
            }
        }

        // Assemble chart data
        foreach ($monthsData as $index => $data) {
            $months->push($data['label']);
            $masukData->push($data['masuk']);
            $keluarData->push($data['keluar']);
            $stokBulananData->push($stokPerBulan[$index]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Inbound Items',
                    'data' => $masukData->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Outbound Items',
                    'data' => $keluarData->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Monthly Stock',
                    'data' => $stokBulananData->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'tension' => 0.3,
                    'borderDash' => [5, 5],
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    public function getHeading(): ?string
    {
        if ($this->filter === 'all' || $this->filter === null) {
            return 'Stock Movement - All Items';
        }
        
        $barang = Barang::find((int) $this->filter);
        return 'Stock Movement - ' . ($barang ? $barang->nama_barang : 'Item');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 10,
                    ],
                ],
            ],
        ];
    }
}
