<?php

namespace App\Filament\Widgets;

use App\Models\Permintaan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UserActivityStats extends BaseWidget
{

    // setengah layar (kalau dashboard 2 kolom)
    protected int | string | array $columnSpan = 1;

    protected function getColumns(): int
    {
        return 1;
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->can('chart_user');
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        Carbon::setLocale('en');


        $totalPermintaan = Permintaan::where('user_id', $user->id)
            ->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();


        return [
            Stat::make('Total Requests', $totalPermintaan)
                ->label('My Requests')
                ->description(
                    'Total submissions in ' . Carbon::now()->translatedFormat('F Y')
                )
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info')
                ->chart($this->getChartData()),
        ];
    }

    public function getChartData(): array
    {
        $user = auth()->user();
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $count = Permintaan::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->count();

            $data[] = $count;
        }

        return $data;
    }
}
