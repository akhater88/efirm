<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'organization_name' => $this->organization_name,
            'display_name' => $this->display_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'nationality' => $this->nationality,
            'tax_registration_number' => $this->tax_registration_number,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'country' => $this->country,
            'is_client' => $this->is_client,
            'is_counterparty' => $this->is_counterparty,
            'notes' => $this->notes,
            'labels' => $this->labels,
            'parent_organization_id' => $this->parent_organization_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
