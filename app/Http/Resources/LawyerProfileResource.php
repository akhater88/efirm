<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LawyerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'bar_admission_number' => $this->bar_admission_number,
            'bar_admission_country' => $this->bar_admission_country,
            'bar_admission_date' => $this->bar_admission_date?->toDateString(),
            'jurisdictions' => $this->jurisdictions,
            'practice_areas' => $this->practice_areas,
            'languages_spoken' => $this->languages_spoken,
            'default_hourly_rate' => $this->default_hourly_rate,
            'default_currency' => $this->default_currency,
            'position_title_ar' => $this->position_title_ar,
            'position_title_en' => $this->position_title_en,
            'bio_ar' => $this->bio_ar,
            'bio_en' => $this->bio_en,
            'status' => $this->status,
            'joined_firm_date' => $this->joined_firm_date?->toDateString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
