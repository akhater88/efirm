<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationAction extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'automation_id',
        'sort_order',
        'action_type',
        'action_payload',
        'stop_on_error',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'action_payload' => 'array',
            'stop_on_error' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}
