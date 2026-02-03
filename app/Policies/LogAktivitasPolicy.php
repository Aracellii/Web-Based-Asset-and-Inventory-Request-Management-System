<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogAktivitasPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('view_log_aktivitas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('clear_log_aktivitas');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('clear_log_aktivitas');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('clear_log_aktivitas');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('clear_log_aktivitas');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LogAktivitas $logAktivitas): bool
    {
        return $user->can('access_log_aktivitas');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('access_log_aktivitas');
    }
}
