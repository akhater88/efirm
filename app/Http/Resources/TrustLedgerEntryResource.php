<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrustLedgerEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trust_account_id' => $this->trust_account_id,
            'type' => $this->type?->value,
            'amount' => $this->amount,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'reference' => $this->reference,
            'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at,
        ];
    }
}
