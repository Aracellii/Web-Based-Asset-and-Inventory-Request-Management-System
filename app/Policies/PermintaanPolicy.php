<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Permintaan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermintaanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // view_any: Bisa lihat semua permintaan
        return $user->can('view_any_permintaan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permintaan $permintaan): bool
    {
        // Jika punya view_any, otomatis bisa view specific record
        if ($user->can('view_any_permintaan')) {
            return true;
        }
        
        // Jika hanya punya view_permintaan
        if ($user->can('view_permintaan')) {
            // Admin bisa view permintaan dari bagiannya
            if ($user->isAdmin()) {
                return $permintaan->user->bagian_id === $user->bagian_id;
            }
            
            // User hanya bisa view permintaannya sendiri
            return $permintaan->user_id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_permintaan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permintaan $permintaan): bool
    {
        // Cek permission dulu
        if (!$user->can('update_permintaan')) {
            return false;
        }
        
        // Super Admin & Keuangan bisa update semua
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return true;
        }
        
        // Admin bisa update permintaan dari bagiannya (untuk approve/reject)
        if ($user->isAdmin()) {
            return $permintaan->user->bagian_id === $user->bagian_id;
        }
        
        // User hanya bisa update permintaannya sendiri jika masih pending
        return $permintaan->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permintaan $permintaan): bool
    {
        // Cek permission dulu
        if (!$user->can('delete_permintaan')) {
            return false;
        }
        
        // Super Admin & Keuangan bisa delete semua
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return true;
        }
        
        // Admin tidak bisa delete permintaan
        // User hanya bisa delete permintaannya sendiri jika masih pending
        if ($user->isUser()) {
            return $permintaan->user_id === $user->id 
                && $permintaan->detailPermintaans()->where('approved', 'pending')->exists();
        }
        
        return false;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_permintaan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Permintaan $permintaan): bool
    {
        return $user->can('force_delete_permintaan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_permintaan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Permintaan $permintaan): bool
    {
        return $user->can('restore_permintaan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_permintaan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Permintaan $permintaan): bool
    {
        return $user->can('replicate_permintaan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_permintaan');
    }
}
