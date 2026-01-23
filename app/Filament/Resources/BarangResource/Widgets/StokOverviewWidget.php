<?php

namespace App\Filament\Resources\BarangResource\Widgets;

use App\Models\Barang;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StokOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];

        // Total Barang
        $stats[] = Stat::make('Total Jenis Barang', Barang::count())
            ->description('Jumlah item dalam katalog')
            ->descriptionIcon('heroicon-o-cube')
            ->color('primary')
            ->chart([7, 3, 4, 5, 6, 3, 5]);

        // Total Stok Keseluruhan
        $totalStok = Gudang::sum('stok');
        $stats[] = Stat::make('Total Stok Keseluruhan', number_format($totalStok))
            ->description('Semua bidang')
            ->descriptionIcon('heroicon-o-archive-box')
            ->color('success')
            ->chart([3, 5, 7, 4, 6, 8, 5]);

        // Stok per Bidang
        $bagians = Bagian::all();
        foreach ($bagians as $bagian) {
            $stokBidang = Gudang::where('bagian_id', $bagian->id)->sum('stok');
            $stats[] = Stat::make($bagian->nama_bagian, number_format($stokBidang))
                ->description('Total stok bidang')
                ->descriptionIcon('heroicon-o-building-office')
                ->color($stokBidang > 20 ? 'success' : ($stokBidang > 5 ? 'warning' : 'danger'));
        }

        return $stats;
    }
}
