<?php

namespace App\Imports;

use App\Services\ExcelImportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Notifications\Notification;
use Exception;

class BarangImporter implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        try {
            // Panggil Service
            $result = ExcelImportService::importBarang($rows);

            // Notifikasi Sukses
            Notification::make()
                ->success()
                ->title('Import Berhasil!')
                ->body($result['message'])
                ->send();

        } catch (Exception $e) {
            // Tampilkan Notifikasi Gagal
            Notification::make()
                ->danger()
                ->title('Import Gagal')
                ->body($e->getMessage()) // Ini akan berisi "Gagal di Baris 1: Bagian 'Tata Boga' tidak ditemukan."
                ->persistent()
                ->send();

            // PENTING: Jangan gunakan 'throw $e' di sini jika tidak ingin halaman error muncul.
            // Biarkan catch ini berakhir dengan tenang agar Livewire tetap menampilkan UI.
            return; 
        }
    }
}