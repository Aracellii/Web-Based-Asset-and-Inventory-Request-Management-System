<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SimplePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

   
                $permissions = [
            'akses_permintaan',
            'buat_permintaan',
            'approve_permintaan',
            'manage_permintaan',

            'akses_stok',
            'manage_stok_barang',
            'export_stok_barang',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

       
    }
}
