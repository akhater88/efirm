<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use Database\Factories\ExternalCalendarEventFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalCalendarEvent extends Model
{
    /** @use HasFactory<ExternalCalendarEventFactory> */
    use BelongsToWorkspace, HasFactory, HasUlids;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'calendar_integration_id',
        'provider_event_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'timezone',
        'is_all_day',
        'attendees',
        'location',
        'linked_matter_id',
        'linked_hearing_id',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
            'attendees' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendarIntegration(): BelongsTo
    {
        return $this->belongsTo(CalendarIntegration::class);
    }

    public function linkedMatter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'linked_matter_id');
    }

    public function linkedHearing(): BelongsTo
    {
        return $this->belongsTo(Hearing::class, 'linked_hearing_id');
    }
}
