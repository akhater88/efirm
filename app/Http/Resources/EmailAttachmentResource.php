<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'attached_to_type' => $this->attached_to_type,
            'attached_to_id' => $this->attached_to_id,
            'email_integration_id' => $this->email_integration_id,
            'email_provider_id' => $this->email_provider_id,
            'subject' => $this->subject,
            'from_address' => $this->from_address,
            'from_name' => $this->from_name,
            'to_addresses' => $this->to_addresses,
            'cc_addresses' => $this->cc_addresses,
            'received_at' => $this->received_at,
            'body_snippet' => $this->body_snippet,
            'has_attachments' => $this->has_attachments,
            'attachment_files' => $this->attachment_files,
            'is_outbound' => $this->is_outbound,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
