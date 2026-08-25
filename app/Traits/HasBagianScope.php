<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filters data by the user's division through the permission system.
 *
 * Permissions:
 * - view_own_division: The user can only see data from their own division.
 * - view_all_divisions: The user can see data from every division.
 *
 * Usage in a Filament Resource:
 * ```php
 * use App\Traits\HasBagianScope;
 *
 * class YourResource extends Resource {
 *     use HasBagianScope;
 *
 *     public static function getEloquentQuery(): Builder {
 *         return static::applyBagianScope(parent::getEloquentQuery(), 'bagian_id');
 *     }
 * }
 * ```
 */
trait HasBagianScope
{
    /**
     * Apply the division scope to the query based on permissions.
     *
     * @param Builder $query
     * @param string $bagianColumn - the division column name (default: 'bagian_id')
     * @return Builder
     */
    public static function applyBagianScope(Builder $query, string $bagianColumn = 'bagian_id'): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // No access if not authenticated
        }

        // Super admins can see everything (permission checks are bypassed).
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Users with view_all_divisions can see all data.
        if ($user->can('view_all_divisions')) {
            return $query;
        }

        // Users with view_own_division are filtered by their division id.
        if ($user->can('view_own_division')) {
            if ($user->bagian_id) {
                return $query->where($bagianColumn, $user->bagian_id);
            }

            // Users without a division id cannot access anything.
            return $query->whereRaw('1 = 0');
        }

        // Default: no permission means no access.
        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply the user scope to the query (for data owned by the user).
     * Used by resources that have a user_id column.
     *
     * @param Builder $query
     * @param string $userColumn - the user column name (default: 'user_id')
     * @return Builder
     */
    public static function applyUserScope(Builder $query, string $userColumn = 'user_id'): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Super admins can see everything.
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Users with view_all_divisions can see all data.
        if ($user->can('view_all_divisions')) {
            return $query;
        }

        // Users with view_own_division
        if ($user->can('view_own_division')) {
            // Admins can see data from their own division.
            if ($user->hasRole('admin') && $user->bagian_id) {
                return $query->whereHas('user', function ($q) use ($user) {
                    $q->where('bagian_id', $user->bagian_id);
                });
            }

            // Regular users can only see their own data.
            return $query->where($userColumn, $user->id);
        }

        // Default: users only see their own data.
        return $query->where($userColumn, $user->id);
    }

    /**
     * Check whether the user can edit/delete a record based on permissions.
     *
     * @param mixed $record - Model instance
     * @param string $ownerColumn - the ownership column ('user_id' or 'bagian_id')
     * @return bool
     */
    public static function canModifyRecord($record, string $ownerColumn = 'bagian_id'): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admins can edit/delete anything.
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Users with view_all_divisions can modify everything.
        if ($user->can('view_all_divisions')) {
            return true;
        }

        // Users with view_own_division must pass the ownership check.
        if ($user->can('view_own_division')) {
            if ($ownerColumn === 'user_id') {
                // Only the owner can modify the record.
                return $record->user_id === $user->id;
            } elseif ($ownerColumn === 'bagian_id') {
                // The user can modify records from their own division.
                return $record->bagian_id === $user->bagian_id;
            }
        }

        return false;
    }
}
