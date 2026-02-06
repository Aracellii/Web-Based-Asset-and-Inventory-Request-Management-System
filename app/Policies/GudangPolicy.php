<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Gudang;
use Illuminate\Auth\Access\HandlesAuthorization;

class GudangPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('akses_stok');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('akses_stok');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Gudang $gudang): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('manage_stok_barang');
    }
}
