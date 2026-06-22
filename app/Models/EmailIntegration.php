<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\EmailIntegrationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailIntegration extends Model
{
    /** @use HasFactory<EmailIntegrationFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'provider',
        'email_address',
        'oauth_access_token',
        'oauth_refresh_token',
        'oauth_expires_at',
        'scopes_granted',
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
            'scopes_granted' => 'array',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailAttachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
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
