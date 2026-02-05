<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_permintaan","buat_permintaan","approve_permintaan","export_permintaan","access_dashboard","akses_stok","view_stok_barang","manage_stok_barang","export_stok_barang","akses_katalog","view_katalog_barang","manage_katalog_barang","import_katalog_barang","export_katalog_barang","akses_permintaan","manage_permintaan","akses_log","view_log_aktivitas","export_log_aktivitas","clear_log_aktivitas","akses_managemen_user","view_manajemen_user","manage_manajemen_user","export_manajemen_user","access_settings","manage_roles","lihat_bagian_sendiri","lihat_semua_bagian","widget_KeuanganActivityStats","widget_AdminActivityStats","widget_UserActivityStats","widget_UserApproved","widget_StockMovementChart","widget_TopRequestedItemsChart"]},{"name":"admin","guard_name":"web","permissions":["view_permintaan","approve_permintaan","export_permintaan","access_dashboard","akses_stok","view_stok_barang","manage_stok_barang","export_stok_barang","akses_katalog","view_katalog_barang","manage_katalog_barang","export_katalog_barang","akses_permintaan","manage_permintaan","akses_log","view_log_aktivitas","export_log_aktivitas","akses_managemen_user","view_manajemen_user","manage_manajemen_user","export_manajemen_user","lihat_semua_bagian","widget_AdminActivityStats","widget_StockMovementChart","widget_TopRequestedItemsChart"]},{"name":"keuangan","guard_name":"web","permissions":["view_permintaan","approve_permintaan","export_permintaan","access_dashboard","akses_stok","view_stok_barang","manage_stok_barang","export_stok_barang","akses_katalog","view_katalog_barang","manage_katalog_barang","import_katalog_barang","export_katalog_barang","akses_permintaan","manage_permintaan","akses_log","view_log_aktivitas","export_log_aktivitas","akses_managemen_user","view_manajemen_user","lihat_semua_bagian","widget_KeuanganActivityStats","widget_StockMovementChart","widget_TopRequestedItemsChart"]},{"name":"user","guard_name":"web","permissions":["view_permintaan","buat_permintaan","access_dashboard","akses_stok","view_stok_barang","akses_katalog","akses_permintaan","akses_log","view_log_aktivitas","lihat_bagian_sendiri","widget_UserActivityStats","widget_UserApproved","widget_TopRequestedItemsChart"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
