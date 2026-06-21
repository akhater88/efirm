<?php

namespace App\Models;

use App\Concerns\BelongsToWorkspace;
use App\Enums\KpiMetric;
use App\Enums\KpiPeriod;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiTarget extends Model
{
    use BelongsToWorkspace, HasUlids, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'targetable_type',
        'targetable_id',
        'metric',
        'target_value',
        'period',
        'effective_from',
        'effective_to',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'metric' => KpiMetric::class,
            'period' => KpiPeriod::class,
            'target_value' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
