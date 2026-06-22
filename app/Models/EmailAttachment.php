<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\EmailAttachmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailAttachment extends Model
{
    /** @use HasFactory<EmailAttachmentFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'attached_to_type',
        'attached_to_id',
        'email_integration_id',
        'email_provider_id',
        'subject',
        'from_address',
        'from_name',
        'to_addresses',
        'cc_addresses',
        'received_at',
        'body_snippet',
        'has_attachments',
        'attachment_files',
        'is_outbound',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'to_addresses' => 'array',
            'cc_addresses' => 'array',
            'received_at' => 'datetime',
            'has_attachments' => 'boolean',
            'attachment_files' => 'array',
            'is_outbound' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function attachedTo(): MorphTo
    {
        return $this->morphTo();
    }

    public function emailIntegration(): BelongsTo
    {
        return $this->belongsTo(EmailIntegration::class);
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
