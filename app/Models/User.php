<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'bagian_id',
    ];
  

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        // Auto-assign Spatie role based on role_id
        static::created(function (User $user) {
            if ($user->role_id) {
                $role = \Spatie\Permission\Models\Role::find($user->role_id);
                if ($role) {
                    $user->assignRole($role->name);
                }
            }
        });

        // Sync role when updated
        static::updated(function (User $user) {
            if ($user->isDirty('role_id') && $user->role_id) {
                $role = \Spatie\Permission\Models\Role::find($user->role_id);
                if ($role) {
                    // Remove all existing roles and assign new one
                    $user->syncRoles([$role->name]);
                }
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    // Helper methods untuk role checking
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isKeuangan(): bool
    {
        return $this->hasRole('keuangan');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }
}
