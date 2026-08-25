<?php

namespace App\Filament\Widgets;

use App\Models\LogAktivitas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinanceActivityStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

   public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->can('chart_finance');
    }

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        Carbon::setLocale('en');

        // Count inbound items this month across all units
        $masukCount = LogAktivitas::where('tipe', 'Inbound')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Count outbound items this month across all units
        $keluarCount = LogAktivitas::where('tipe', 'Outbound')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            Stat::make('Inbound Items', $masukCount)
                ->description('Month ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->chart($this->getChartData('Inbound')),

            Stat::make('Outbound Items', $keluarCount)
                ->description('Month ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('danger')
                ->chart($this->getChartData('Outbound')),
        ];
    }

    protected function getChartData(string $tipe): array
    {
        $data = [];

        // Collect the last 7 days across all units
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
