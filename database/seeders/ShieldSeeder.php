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

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["access_catalog","access_log","access_user_management","access_request","access_stock","approve_request","create_request","export_stock","view_own_division","view_all_divisions","manage_item_catalog","manage_user_management","manage_request","manage_roles","manage_stock"]},{"name":"admin","guard_name":"web","permissions":["access_catalog","access_log","access_user_management","access_request","access_stock","approve_request","export_stock","view_all_divisions","manage_item_catalog","manage_user_management","manage_request","manage_stock"]},{"name":"finance","guard_name":"web","permissions":["access_catalog","access_log","access_user_management","access_request","access_stock","approve_request","export_stock","view_all_divisions","manage_item_catalog","manage_request","manage_stock"]},{"name":"user","guard_name":"web","permissions":["access_catalog","access_log","access_request","access_stock","create_request","view_own_division"]}]';
        $directPermissions = '[{"name":"chart_admin","guard_name":"web"},{"name":"chart_finance","guard_name":"web"},{"name":"chart_stock","guard_name":"web"},{"name":"chart_request","guard_name":"web"},{"name":"chart_user","guard_name":"web"}]';

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
