<?php

namespace App\Filament\Resources\GudangResource\Pages;

use App\Filament\Resources\GudangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use PhpParser\Node\Stmt\Label;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokBarangExport;

class ListGudangs extends ListRecords
{
    protected static string $resource = GudangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                ->action(function () {
                    $gudangs = \App\Models\Gudang::with(['barang', 'bagian'])->get();
                    
                    // Group by bagian
                    $groupedRecords = $gudangs->groupBy(function($item) {
                        return $item->bagian->nama_bagian ?? 'Tanpa Bagian';
                    });
                    
                    $pdf = Pdf::loadView('pdf.stok-barang', [
                        'title' => 'Laporan Stok Barang',
                        'groupedRecords' => $groupedRecords,
                        'tanggal' => now()->format('d F Y'),
                    ])->setPaper('a4', 'landscape');
                    
                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        'stok-barang-' . now()->format('Y-m-d') . '.pdf'
                    );
                }),
                
            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn() => in_array(auth()->user()?->role, ['keuangan', 'admin']))
                ->action(function () {
                    return Excel::download(
                        new StokBarangExport(),
                        'stok-barang-' . now()->format('Y-m-d') . '.xlsx'
                    );
                }),
                
            Actions\CreateAction::make()
                ->label('Tambah Stok')
                ->icon('heroicon-o-plus')
                ->size('xl'),
        ];
    }
}
