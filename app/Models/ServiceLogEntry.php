<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\ServiceMethod;
use App\Enums\ServiceStatus;
use Database\Factories\ServiceLogEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceLogEntry extends Model
{
    /** @use HasFactory<ServiceLogEntryFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'matter_id',
        'served_party_contact_id',
        'service_method',
        'service_date',
        'service_address',
        'served_by_name',
        'served_to_recipient_name',
        'proof_document_id',
        'status',
        'notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'service_method' => ServiceMethod::class,
            'status' => ServiceStatus::class,
            'service_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function servedParty(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'served_party_contact_id');
    }

    public function proofDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'proof_document_id');
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
