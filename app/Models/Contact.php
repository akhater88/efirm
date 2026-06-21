<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'type',
        'first_name',
        'middle_name',
        'last_name',
        'organization_name',
        'display_name',
        'email',
        'phone',
        'nationality',
        'tax_registration_number',
        'address_line_1',
        'address_line_2',
        'city',
        'country',
        'is_client',
        'is_counterparty',
        'notes',
        'labels',
        'parent_organization_id',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_client' => 'boolean',
            'is_counterparty' => 'boolean',
            'labels' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Contact $contact) {
            $contact->display_name = $contact->computeDisplayName();
        });

        static::updating(function (Contact $contact) {
            $contact->display_name = $contact->computeDisplayName();
        });
    }

    public function computeDisplayName(): string
    {
        if ($this->type === 'organization') {
            return $this->organization_name ?? '';
        }

        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    // --- Relationships ---

    public function parentOrganization(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_organization_id');
    }

    public function peopleInOrganization(): HasMany
    {
        return $this->hasMany(self::class, 'parent_organization_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    // --- Scopes ---

    public function scopePerson($query)
    {
        return $query->where('type', 'person');
    }

    public function scopeOrganization($query)
    {
        return $query->where('type', 'organization');
    }

    public function scopeClient($query)
    {
        return $query->where('is_client', true);
    }

    public function scopeCounterparty($query)
    {
        return $query->where('is_counterparty', true);
    }
}
