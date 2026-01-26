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
    protected static ?string $heading = 'Pergerakan Stok';
    
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Semua Barang'];
        
        $user = Auth::user();
        
        // Jika bukan keuangan, hanya tampilkan barang yang ada di gudang bagian user
        if ($user->role !== 'keuangan') {
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

        // Get date range for last 6 months
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Calculate months between start and end
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        // Get the selected barang_id
        $barangId = ($this->filter !== 'all' && $this->filter !== null) ? (int) $this->filter : null;

        // Get user's bagian for filtering
        $user = Auth::user();
        $gudangIds = null;
        
        // Jika bukan keuangan, filter berdasarkan gudang dari bagian user
        if ($user->role !== 'keuangan') {
            $gudangIds = Gudang::where('bagian_id', $user->bagian_id)
                ->pluck('id')
                ->toArray();
        }

        while ($current <= $end) {
            $monthLabel = $current->translatedFormat('M Y');
            $months->push($monthLabel);

            // Build base query for this month
            $masukQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '>', 'stok_awal');

            $keluarQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '<', 'stok_awal');

            // Filter by gudang (bagian) if not keuangan
            if ($gudangIds !== null) {
                $masukQuery->whereIn('gudang_id', $gudangIds);
                $keluarQuery->whereIn('gudang_id', $gudangIds);
            }

            // Filter by barang if not 'all'
            if ($barangId !== null) {
                $masukQuery->where('barang_id', $barangId);
                $keluarQuery->where('barang_id', $barangId);
            }

            // Barang masuk = stok_akhir > stok_awal (termasuk penyesuaian yang menambah)
            $masuk = $masukQuery->sum('jumlah');

            // Barang keluar = stok_akhir < stok_awal (termasuk penyesuaian yang mengurangi)
            $keluar = $keluarQuery->sum('jumlah');

            $masukData->push($masuk);
            $keluarData->push($keluar);

            $current->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $masukData->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $keluarData->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    public function getHeading(): ?string
    {
        if ($this->filter === 'all' || $this->filter === null) {
            return 'Pergerakan Stok - Semua Barang';
        }
        
        $barang = Barang::find((int) $this->filter);
        return 'Pergerakan Stok - ' . ($barang ? $barang->nama_barang : 'Barang');
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
