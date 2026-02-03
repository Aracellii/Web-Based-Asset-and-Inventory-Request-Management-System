<?php

namespace App\Filament\Widgets;

use App\Models\Permintaan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UserActivityStats extends BaseWidget
{
    protected static ?int $sort = 1;

    // setengah layar (kalau dashboard 2 kolom)
    protected int | string | array $columnSpan = 1;

    protected function getColumns(): int
    {
        return 1;
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('widget_UserActivityStats');
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        Carbon::setLocale('id');

        $totalPermintaan = Permintaan::where('user_id', $user->id)
            ->whereBetween('tanggal_permintaan', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->count();

        return [
            Stat::make('Total Permintaan', $totalPermintaan)
                ->label('Permintaan Saya')
                ->description(
                    'Total pengajuan di bulan ' . Carbon::now()->translatedFormat('F Y')
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
                ->whereDate('tanggal_permintaan', $date)
                ->count();

            $data[] = $count;
        }

        return $data;
    }
}
