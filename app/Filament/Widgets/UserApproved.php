<?php

namespace App\Filament\Widgets;

use App\Models\DetailPermintaan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class UserApproved extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 1;

    protected function getColumns(): int
    {
        return 1;
    }

       public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->can('user_graphic');
    }


    protected function getStats(): array
    {
        $user = auth()->user();

        $approvedCount = DetailPermintaan::join(
                'permintaans',
                'detail_permintaans.permintaan_id',
                '=',
                'permintaans.id'
            )
            ->where('permintaans.user_id', $user->id)
            ->where('detail_permintaans.approved', 'approved')
            ->count();

        return [
            Stat::make('Barang Disetujui', $approvedCount)
                ->label('Barang Saya Disetujui')
                ->description('Total item barang yang telah di-approve')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart($this->getApprovedChartData()),
        ];
    }

    public function getApprovedChartData(): array
    {
        $user = auth()->user();
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();

            $count = DetailPermintaan::join(
                    'permintaans',
                    'detail_permintaans.permintaan_id',
                    '=',
                    'permintaans.id'
                )
                ->where('permintaans.user_id', $user->id)
                ->where('detail_permintaans.approved', 'approved')
                ->whereDate('detail_permintaans.updated_at', $date)
                ->count();

            $data[] = $count;
        }

        return $data;
    }
}
