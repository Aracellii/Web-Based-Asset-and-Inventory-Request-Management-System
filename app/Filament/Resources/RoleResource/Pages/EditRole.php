<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissions = collect($data)
            ->filter(function ($permission, $key) {
                return ! in_array($key, ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()]);
            })
            ->values()
            ->flatten()
            ->unique();

        if (Arr::has($data, Utils::getTenantModelForeignKey())) {
            return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name']);
    }
     protected function getCreateFormAction(): Actions\Action
        {
            return parent::getCreateFormAction()
                ->label('Simpan Perubahan');
        }
    protected function afterSave(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function ($permission) use ($permissionModels) {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        // Untuk super_admin, pastikan permission role resource tetap ada
        if ($this->record->name === 'super_admin') {
            $rolePermissions = [
                'manage_roles',
                'akses_managemen_user',
                'manage_managemen_user',
                'akses_log',
            ];
            foreach ($rolePermissions as $perm) {
                $permModel = Utils::getPermissionModel()::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => $this->data['guard_name'],
                ]);
                if (!$permissionModels->contains('id', $permModel->id)) {
                    $permissionModels->push($permModel);
                }
            }
        }

        $this->record->syncPermissions($permissionModels);
        
        // Clear permission cache dan redirect untuk refresh
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
    }
}
