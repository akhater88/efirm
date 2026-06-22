<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'contact_id' => $this->contact_id,
            'matter_id' => $this->matter_id,
            'status' => $this->status?->value,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'amount_paid' => $this->amount_paid,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'lines' => InvoiceLineResource::collection($this->whenLoaded('lines')),
            'receipts' => ReceiptResource::collection($this->whenLoaded('receipts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
