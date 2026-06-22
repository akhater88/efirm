<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceLogEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matter_id' => $this->matter_id,
            'served_party_contact_id' => $this->served_party_contact_id,
            'served_party' => new ContactResource($this->whenLoaded('servedParty')),
            'service_method' => $this->service_method?->value,
            'service_date' => $this->service_date?->toDateString(),
            'service_address' => $this->service_address,
            'served_by_name' => $this->served_by_name,
            'served_to_recipient_name' => $this->served_to_recipient_name,
            'proof_document_id' => $this->proof_document_id,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
