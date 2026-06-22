<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'receipt_number' => $this->receipt_number,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method?->value,
            'received_date' => $this->received_date,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
