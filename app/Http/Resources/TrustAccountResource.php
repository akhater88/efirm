<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrustAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contact_id,
            'matter_id' => $this->matter_id,
            'name' => $this->name,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'currency' => $this->currency,
            'balance' => $this->balance,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'ledger_entries' => TrustLedgerEntryResource::collection($this->whenLoaded('ledgerEntries')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
