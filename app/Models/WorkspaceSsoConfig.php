<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\WorkspaceSsoConfigFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceSsoConfig extends Model
{
    /** @use HasFactory<WorkspaceSsoConfigFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'provider_type',
        'provider_name',
        'idp_metadata_url',
        'idp_metadata_xml',
        'idp_entity_id',
        'idp_sso_url',
        'idp_certificate',
        'sp_entity_id',
        'attribute_mapping',
        'enforce_for_domain',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'idp_certificate' => 'encrypted',
            'attribute_mapping' => 'array',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
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
