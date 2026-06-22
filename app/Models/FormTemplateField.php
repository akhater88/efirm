<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormTemplateField extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'form_template_id',
        'key',
        'label_ar',
        'label_en',
        'field_type',
        'is_required',
        'default_value',
        'options',
        'validation_rules',
        'help_text_ar',
        'help_text_en',
        'sort_order',
        'is_pii',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'default_value' => 'array',
            'options' => 'array',
            'validation_rules' => 'array',
            'sort_order' => 'integer',
            'is_pii' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }
}
