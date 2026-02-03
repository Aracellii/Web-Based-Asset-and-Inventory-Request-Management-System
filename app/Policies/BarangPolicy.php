<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Barang;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarangPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // view_any: Bisa lihat semua barang
        return $user->can('view_any_barang');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Barang $barang): bool
    {
        // Jika punya view_any, otomatis bisa view specific record
        if ($user->can('view_any_barang')) {
            return true;
        }
        
        // Jika hanya punya view, cek apakah barang ada di gudang bagiannya
        if ($user->can('view_barang') && $user->bagian_id) {
            return $barang->gudangs()->where('bagian_id', $user->bagian_id)->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya Keuangan & Super Admin yang bisa create barang baru
        return $user->can('create_barang') && ($user->isSuperAdmin() || $user->isKeuangan());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Barang $barang): bool
    {
        // Hanya Keuangan & Super Admin yang bisa update barang
        return $user->can('update_barang') && ($user->isSuperAdmin() || $user->isKeuangan());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Barang $barang): bool
    {
        // Hanya Keuangan & Super Admin yang bisa delete barang
        return $user->can('delete_barang') && ($user->isSuperAdmin() || $user->isKeuangan());
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_barang');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Barang $barang): bool
    {
        return $user->can('force_delete_barang');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_barang');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Barang $barang): bool
    {
        return $user->can('restore_barang');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_barang');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Barang $barang): bool
    {
        return $user->can('replicate_barang');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_barang');
    }
}
