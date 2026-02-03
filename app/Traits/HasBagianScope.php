<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait untuk filtering data berdasarkan bagian user
 * 
 * Usage:
 * - Super Admin: Lihat semua data
 * - Keuangan: Lihat semua data
 * - Admin: Lihat data bagiannya saja
 * - User: Lihat data bagiannya saja (atau bisa lebih restrictive)
 */
trait HasBagianScope
{
    /**
     * Apply bagian scope ke query
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

        // Super Admin & Keuangan dapat melihat semua data
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return $query;
        }

        // Admin dan User hanya dapat melihat data bagiannya
        if ($user->bagian_id) {
            return $query->where($bagianColumn, $user->bagian_id);
        }

        // Jika user tidak punya bagian_id, tidak bisa akses apa-apa
        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply user scope ke query (untuk data yang dimiliki user sendiri)
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

        // Super Admin & Keuangan dapat melihat semua data
        if ($user->isSuperAdmin() || $user->isKeuangan()) {
            return $query;
        }

        // Admin dapat melihat data dari bagiannya
        if ($user->isAdmin() && $user->bagian_id) {
            // Jika ada relasi ke user, filter by bagian
            return $query->whereHas('user', function ($q) use ($user) {
                $q->where('bagian_id', $user->bagian_id);
            });
        }

        // User biasa hanya dapat melihat data miliknya sendiri
        return $query->where($userColumn, $user->id);
    }

    /**
     * Check apakah user bisa edit/delete record tertentu
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
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Keuangan bisa edit/delete apapun (sesuai permission)
        if ($user->isKeuangan()) {
            return true;
        }

        // Check berdasarkan ownership column
        if ($ownerColumn === 'user_id') {
            // Hanya owner yang bisa modify
            return $record->user_id === $user->id;
        } elseif ($ownerColumn === 'bagian_id') {
            // Admin bisa modify data dari bagiannya
            return $record->bagian_id === $user->bagian_id;
        }

        return false;
    }
}
