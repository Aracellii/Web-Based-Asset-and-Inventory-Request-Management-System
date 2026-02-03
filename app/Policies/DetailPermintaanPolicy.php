<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DetailPermintaan;
use Illuminate\Auth\Access\HandlesAuthorization;

class DetailPermintaanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_detail::permintaan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DetailPermintaan $detailPermintaan): bool
    {
        // Cek permission dulu
        if (!$user->can('view_detail::permintaan')) {
            return false;
        }
        
        // Super Admin & Keuangan bisa view semua
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return true;
        }
        
        // Admin bisa view detail permintaan dari bagiannya
        if ($user->isAdmin()) {
            return $detailPermintaan->permintaan->user->bagian_id === $user->bagian_id;
        }
        
        // User hanya bisa view detail permintaannya sendiri
        return $detailPermintaan->permintaan->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_detail::permintaan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DetailPermintaan $detailPermintaan): bool
    {
        // Cek permission dulu
        if (!$user->can('update_detail::permintaan')) {
            return false;
        }
        
        // Super Admin & Keuangan bisa update semua
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return true;
        }
        
        // Admin bisa update untuk approve/reject
        if ($user->isAdmin()) {
            return $detailPermintaan->permintaan->user->bagian_id === $user->bagian_id;
        }
        
        // User hanya bisa update detail permintaannya sendiri jika masih pending
        return $detailPermintaan->permintaan->user_id === $user->id 
            && $detailPermintaan->approved === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DetailPermintaan $detailPermintaan): bool
    {
        // Cek permission dulu
        if (!$user->can('delete_detail::permintaan')) {
            return false;
        }
        
        // Super Admin & Keuangan bisa delete semua
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return true;
        }
        
        // Admin tidak bisa delete detail permintaan
        // User hanya bisa delete detail permintaannya sendiri jika masih pending
        return $detailPermintaan->permintaan->user_id === $user->id 
            && $detailPermintaan->approved === 'pending';
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_detail::permintaan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DetailPermintaan $detailPermintaan): bool
    {
        return $user->can('force_delete_detail::permintaan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_detail::permintaan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DetailPermintaan $detailPermintaan): bool
    {
        return $user->can('restore_detail::permintaan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_detail::permintaan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DetailPermintaan $detailPermintaan): bool
    {
        return $user->can('replicate_detail::permintaan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_detail::permintaan');
    }
}
