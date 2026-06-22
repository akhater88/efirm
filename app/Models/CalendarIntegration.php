<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\CalendarIntegrationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarIntegration extends Model
{
    /** @use HasFactory<CalendarIntegrationFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'provider',
        'calendar_id',
        'oauth_access_token',
        'oauth_refresh_token',
        'oauth_expires_at',
        'is_active',
        'last_synced_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'oauth_access_token' => 'encrypted',
            'oauth_refresh_token' => 'encrypted',
            'oauth_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ExternalCalendarEvent::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
