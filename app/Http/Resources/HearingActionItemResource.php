<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HearingActionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hearing_id' => $this->hearing_id,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'due_date' => $this->due_date?->toDateString(),
            'responsible_user_id' => $this->responsible_user_id,
            'status' => $this->status,
            'obligation_id' => $this->obligation_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
