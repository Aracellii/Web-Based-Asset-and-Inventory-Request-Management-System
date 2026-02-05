<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait untuk filtering data berdasarkan bagian user dengan permission system
 * 
 * Permissions:
 * - lihat_bagian_sendiri: User hanya bisa lihat data bagiannya sendiri
 * - lihat_semua_bagian: User bisa lihat data semua bagian
 * 
 * Usage di Filament Resource:
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
     * Apply bagian scope ke query berdasarkan permission
     * 
     * @param Builder $query
     * @param string $bagianColumn - nama kolom bagian_id (default: 'bagian_id')
     * @return Builder
     */
    public static function applyBagianScope(Builder $query, string $bagianColumn = 'bagian_id'): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // No access if not authenticated
        }

        // Super Admin dapat melihat semua data (bypass permission check)
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Jika user punya permission lihat_semua_bagian, bisa lihat semua data
        if ($user->can('lihat_semua_bagian')) {
            return $query;
        }

        // Jika user punya permission lihat_bagian_sendiri, filter by bagian_id
        if ($user->can('lihat_bagian_sendiri')) {
            if ($user->bagian_id) {
                return $query->where($bagianColumn, $user->bagian_id);
            }
            // Jika user tidak punya bagian_id, tidak bisa akses apa-apa
            return $query->whereRaw('1 = 0');
        }

        // Default: jika tidak punya permission apapun, tidak bisa akses
        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply user scope ke query (untuk data yang dimiliki user sendiri)
     * Digunakan untuk resource yang memiliki kolom user_id
     * 
     * @param Builder $query
     * @param string $userColumn - nama kolom user_id (default: 'user_id')
     * @return Builder
     */
    public static function applyUserScope(Builder $query, string $userColumn = 'user_id'): Builder
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Super Admin dapat melihat semua data
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Jika user punya permission lihat_semua_bagian, bisa lihat semua data
        if ($user->can('lihat_semua_bagian')) {
            return $query;
        }

        // Jika user punya permission lihat_bagian_sendiri
        if ($user->can('lihat_bagian_sendiri')) {
            // Admin dapat melihat data dari bagiannya
            if ($user->hasRole('admin') && $user->bagian_id) {
                return $query->whereHas('user', function ($q) use ($user) {
                    $q->where('bagian_id', $user->bagian_id);
                });
            }
            
            // User biasa hanya dapat melihat data miliknya sendiri
            return $query->where($userColumn, $user->id);
        }

        // Default: User hanya lihat data sendiri
        return $query->where($userColumn, $user->id);
    }

    /**
     * Check apakah user bisa edit/delete record tertentu berdasarkan permission
     * 
     * @param mixed $record - Model instance
     * @param string $ownerColumn - kolom yang menunjukkan ownership ('user_id' atau 'bagian_id')
     * @return bool
     */
    public static function canModifyRecord($record, string $ownerColumn = 'bagian_id'): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Super Admin bisa edit/delete apapun
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Jika punya permission lihat_semua_bagian, bisa modify semua
        if ($user->can('lihat_semua_bagian')) {
            return true;
        }

        // Jika punya permission lihat_bagian_sendiri, cek ownership
        if ($user->can('lihat_bagian_sendiri')) {
            if ($ownerColumn === 'user_id') {
                // Hanya owner yang bisa modify
                return $record->user_id === $user->id;
            } elseif ($ownerColumn === 'bagian_id') {
                // Bisa modify data dari bagiannya
                return $record->bagian_id === $user->bagian_id;
            }
        }

        return false;
    }
}
