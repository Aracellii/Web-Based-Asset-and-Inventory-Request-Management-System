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

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        return auth()->user()?->role !== 'user';
    }

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
        $stokBulananData = collect();

        // Get date range for last 12 months 
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Calculate months between start and end
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->endOfMonth();

        // barang_id
        $barangId = ($this->filter !== 'all' && $this->filter !== null) ? (int) $this->filter : null;

        // Filter User Bagian
        $user = Auth::user();
        $gudangIds = null;
        $bagianId = null;
        
        // Jika bukan keuangan, filter berdasarkan gudang dari bagian user
        if ($user->role !== 'keuangan') {
            $bagianId = $user->bagian_id;
            $gudangIds = Gudang::where('bagian_id', $bagianId)
                ->pluck('id')
                ->toArray();
        }

        // Ambil stok saat ini dari tabel gudangs
        $stokSekarangQuery = Gudang::query();
        if ($bagianId !== null) {
            $stokSekarangQuery->where('bagian_id', $bagianId);
        }
        if ($barangId !== null) {
            $stokSekarangQuery->where('barang_id', $barangId);
        }
        $stokSekarang = $stokSekarangQuery->sum('stok');

        // Kumpulkan data per bulan terlebih 
        $monthsData = collect();
        while ($current <= $end) {
            $monthLabel = $current->translatedFormat('M Y');
            
            // query bulan ini 
            $masukQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '>', 'stok_awal');

            $keluarQuery = LogAktivitas::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->whereColumn('stok_akhir', '<', 'stok_awal');

            // Filter by gudang (bagian) bukan keuangan
            if ($gudangIds !== null) {
                $masukQuery->whereIn('gudang_id', $gudangIds);
                $keluarQuery->whereIn('gudang_id', $gudangIds);
            }

            // Filter by barang if not 'all'
            if ($barangId !== null) {
                $masukQuery->where('barang_id', $barangId);
                $keluarQuery->where('barang_id', $barangId);
            }

            // Barang masuk = stok_akhir > stok_awal
            $masuk = $masukQuery->sum('jumlah');

            // Barang keluar = stok_akhir < stok_awal
            $keluar = $keluarQuery->sum('jumlah');

            $monthsData->push([
                'label' => $monthLabel,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'isCurrent' => $current->isSameMonth(Carbon::now()),
            ]);

            $current->addMonth();
        }

        // Hitung stok mundur dari stok sekarang
        $stokKumulatif = $stokSekarang;
        $reversedMonths = $monthsData->reverse()->values();
        $stokPerBulan = collect();

        foreach ($reversedMonths as $index => $data) {
            if ($index === 0) {
                // Bulan terakhir (sekarang)
                $stokPerBulan->prepend($stokKumulatif);
            } else {
                // Bulan sebelumnya: stok = stok bulan depan - masuk bulan depan + keluar bulan depan
                $prevData = $reversedMonths[$index - 1];
                $stokKumulatif = $stokKumulatif - $prevData['masuk'] + $prevData['keluar'];
                $stokPerBulan->prepend($stokKumulatif);
            }
        }

        // Susun data chart
        foreach ($monthsData as $index => $data) {
            $months->push($data['label']);
            $masukData->push($data['masuk']);
            $keluarData->push($data['keluar']);
            $stokBulananData->push($stokPerBulan[$index]);
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
                [
                    'label' => 'Stok Tiap Bulan',
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
