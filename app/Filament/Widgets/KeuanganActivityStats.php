<?php

namespace App\Filament\Widgets;

use App\Models\LogAktivitas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class KeuanganActivityStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('widget_KeuanganActivityStats');
    }

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        Carbon::setLocale('id');

        // Hitung jumlah barang masuk bulan ini dari SEMUA bagian
        $masukCount = LogAktivitas::where('tipe', 'Masuk')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Hitung jumlah barang keluar bulan ini dari SEMUA bagian
        $keluarCount = LogAktivitas::where('tipe', 'Keluar')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            Stat::make('Barang Masuk', $masukCount)
                ->description('Bulan ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->chart($this->getChartData('Masuk')),

            Stat::make('Barang Keluar', $keluarCount)
                ->description('Bulan ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger')
                ->chart($this->getChartData('Keluar')),
        ];
    }

    protected function getChartData(string $tipe): array
    {
        $data = [];

        // Ambil data 7 hari terakhir dari SEMUA bagian
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = LogAktivitas::where('tipe', $tipe)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
