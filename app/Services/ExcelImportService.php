<?php

namespace App\Services;

use App\Imports\BarangImporter;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ExcelImportService
{
    public static function importBarang($filePath)
    {
        return DB::transaction(function () use ($filePath) {
            $importer = new BarangImporter();
            
            try {
                Excel::import($importer, $filePath);

                // Jika ada error, rollback otomatis karena transaction
                if (count($importer->errors) > 0) {
                    throw new Exception(implode("\n", $importer->errors));
                }

                return [
                    'success' => true,
                    'count' => $importer->successCount,
                    'message' => "Total {$importer->successCount} barang berhasil diimport."
                ];

            } catch (Exception $e) {
                // Transaction akan otomatis rollback
                throw new Exception("Import gagal: " . $e->getMessage());
            }
        });
    }
}
