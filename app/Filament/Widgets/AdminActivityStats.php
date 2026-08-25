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
        $user = auth()->user();
        return $user && $user->can('chart_admin');
    }

    protected function getStats(): array
    {

        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        Carbon::setLocale('id');

        // Count outbound actions
        $approveCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Outbound')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Count inbound items this month
        $masukCount = LogAktivitas::where('user_id', $user->id)
            ->where('tipe', 'Inbound')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return [
            Stat::make('Request Approvals', $approveCount)
                ->description('Month ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getChartData('Outbound')),

            Stat::make('Inbound Items', $masukCount)
                ->description('Month ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->chart($this->getChartData('Inbound')),
        ];
    }

    protected function getChartData(string $tipe): array
    {
        $user = auth()->user();
        $data = [];

        // Collect data
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
