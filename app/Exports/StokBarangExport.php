<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\Bagian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokBarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $rowNumber = 0;

    public function collection()
    {
        return Barang::with(['gudangs.bagian'])->orderBy('nama_barang')->get();
    }

    public function headings(): array
    {
        $bagians = Bagian::orderBy('nama_bagian')->get();
        
        $headings = ['No', 'Kode Barang', 'Nama Barang'];
        
        foreach ($bagians as $bagian) {
            $headings[] = $bagian->nama_bagian;
        }
        
        $headings[] = 'Total Stok';
        
        return $headings;
    }

    public function map($barang): array
    {
        $this->rowNumber++;
        $bagians = Bagian::orderBy('nama_bagian')->get();
        
        $row = [
            $this->rowNumber,
            $barang->kode_barang,
            $barang->nama_barang,
        ];
        
        $totalStok = 0;
        foreach ($bagians as $bagian) {
            $gudang = $barang->gudangs->firstWhere('bagian_id', $bagian->id);
            $stok = $gudang ? $gudang->stok : 0;
            $row[] = $stok;
            $totalStok += $stok;
        }
        
        $row[] = $totalStok;
        
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Stok Barang';
    }
}
