<?php

namespace App\Models;

use App\Enums\AdminRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable implements FilamentUser
{
    /** @var string */
    protected $table = 'admin_users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'locale',
        'last_login_at',
        'disabled_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'role' => AdminRole::class,
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    // --- Filament ---

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'platform-admin' && $this->disabled_at === null;
    }

    // --- Relationships ---

    /** @return HasMany<AdminActivityLog, $this> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_user_id');
    }

    // --- Helpers ---

    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SuperAdmin;
    }

    public function isDisabled(): bool
    {
        return $this->disabled_at !== null;
    }

    // --- Scopes ---

    /** @param Builder<AdminUser> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('disabled_at');
    }

    /** @param Builder<AdminUser> $query */
    public function scopeSuperAdmins(Builder $query): Builder
    {
        return $query->where('role', AdminRole::SuperAdmin);
    }
}
