<?php

namespace App\Filament\Widgets;

use App\Models\LogAktivitas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AdminActivityStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['admin']);
    }

    protected function getStats(): array
    {

        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        Carbon::setLocale('id');

        // Hitung jumlah approve 
        $approveCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Keluar')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Hitung jumlah barang masuk bulan ini 
        $masukCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Masuk')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            Stat::make('Approve Permintaan', $approveCount)
                ->description('Bulan ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getChartData('Keluar')),

            Stat::make('Barang Masuk', $masukCount)
                ->description('Bulan ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->chart($this->getChartData('Masuk')),
        ];
    }

    protected function getChartData(string $tipe): array
    {
        $user = auth()->user();
        $data = [];

        // Ambil data 
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
