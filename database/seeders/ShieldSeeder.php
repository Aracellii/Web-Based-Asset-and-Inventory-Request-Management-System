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

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["akses_katalog","akses_log","akses_managemen_user","akses_permintaan","akses_stok","approve_permintaan","buat_permintaan","export_stok_barang","lihat_bagian_sendiri","lihat_semua_bagian","manage_katalog_barang","manage_manajemen_user","manage_permintaan","manage_roles","manage_stok_barang"]},{"name":"admin","guard_name":"web","permissions":["akses_katalog","akses_log","akses_managemen_user","akses_permintaan","akses_stok","approve_permintaan","export_stok_barang","lihat_semua_bagian","manage_katalog_barang","manage_manajemen_user","manage_permintaan","manage_stok_barang"]},{"name":"keuangan","guard_name":"web","permissions":["akses_katalog","akses_log","akses_managemen_user","akses_permintaan","akses_stok","approve_permintaan","export_stok_barang","lihat_semua_bagian","manage_katalog_barang","manage_permintaan","manage_stok_barang"]},{"name":"user","guard_name":"web","permissions":["akses_katalog","akses_log","akses_permintaan","akses_stok","buat_permintaan","lihat_bagian_sendiri"]}]';
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
