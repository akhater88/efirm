<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'court_type' => $this->court_type?->value,
            'jurisdiction_country' => $this->jurisdiction_country,
            'jurisdiction_governorate' => $this->jurisdiction_governorate,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'judges' => JudgeResource::collection($this->whenLoaded('judges')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
