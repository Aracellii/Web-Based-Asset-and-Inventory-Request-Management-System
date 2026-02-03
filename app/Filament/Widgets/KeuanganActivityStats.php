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
        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        Carbon::setLocale('id');

        // Hitung jumlah barang masuk bulan ini 
        $masukCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Masuk')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Hitung jumlah barang keluar bulan ini
        $keluarCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Keluar')
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
        $user = auth()->user();
        $data = [];

        // Ambil data 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = LogAktivitas::where('user_id', $user->id)
                ->where('tipe', $tipe)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
