<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Bagian;
use App\Models\Gudang;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Barang')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $stokKosongCount = \App\Models\Barang::whereIn('id', function ($query) {
            $query->select('barang_id')
                ->from('gudangs')
                ->where('stok', 0);
        })->count();

        return [
            'semua' => Tab::make('Semua Barang')
                ->icon('heroicon-o-cube'),
            'stok_kosong' => Tab::make('Stok Kosong')
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('id', function ($subQuery) {
                    $subQuery->select('barang_id')
                        ->from('gudangs')
                        ->where('stok', 0);
                }))
                ->badge($stokKosongCount > 0 ? $stokKosongCount : null)
                ->badgeColor('danger'),
        ];
    }
}
