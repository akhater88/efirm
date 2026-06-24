<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price_per_seat_usd',
        'max_seats',
        'max_matters',
        'max_contacts',
        'max_storage_mb',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_per_seat_usd' => 'decimal:2',
            'max_seats' => 'integer',
            'max_matters' => 'integer',
            'max_contacts' => 'integer',
            'max_storage_mb' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
    }

    public function localizedDescription(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
