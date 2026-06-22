<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'preferred_locale',
        'google_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relationships ---

    public function workspaceMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function lawyerProfile(): HasOne
    {
        return $this->hasOne(LawyerProfile::class);
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // --- Workspace helpers ---

    public function currentWorkspace(): ?Workspace
    {
        $workspaceId = session('current_workspace_id');

        if ($workspaceId) {
            return $this->workspaces()->where('workspaces.id', $workspaceId)->first();
        }

        return $this->workspaces()->first();
    }

    public function switchWorkspace(Workspace $workspace): void
    {
        session(['current_workspace_id' => $workspace->id]);
    }

    public function roleInWorkspace(Workspace $workspace): ?Role
    {
        $member = $this->workspaceMembers()
            ->where('workspace_id', $workspace->id)
            ->first();

        return $member?->role;
    }

    public function currentRole(): ?Role
    {
        $workspace = $this->currentWorkspace();
        if (! $workspace) {
            return null;
        }

        return $this->roleInWorkspace($workspace);
    }

    public function belongsToWorkspace(Workspace $workspace): bool
    {
        return $this->workspaceMembers()
            ->where('workspace_id', $workspace->id)
            ->exists();
    }

    public function isLawyer(): bool
    {
        return $this->lawyerProfile()->where('status', 'active')->exists();
    }

    public function scopeLawyers($query)
    {
        return $query->whereHas('lawyerProfile', fn ($q) => $q->where('status', 'active'));
    }

    // --- Filament interfaces ---

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->workspaceMembers()->exists();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->workspaces()->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->workspaces()->where('workspaces.id', $tenant->id)->exists();
    }
}
