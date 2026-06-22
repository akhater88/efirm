<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExternalCalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'user_id' => $this->user_id,
            'calendar_integration_id' => $this->calendar_integration_id,
            'provider_event_id' => $this->provider_event_id,
            'title' => $this->title,
            'description' => $this->description,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'timezone' => $this->timezone,
            'is_all_day' => $this->is_all_day,
            'attendees' => $this->attendees,
            'location' => $this->location,
            'linked_matter_id' => $this->linked_matter_id,
            'linked_hearing_id' => $this->linked_hearing_id,
            'last_synced_at' => $this->last_synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
