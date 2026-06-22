<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCalendarIntegrationRequest;
use App\Http\Requests\UpdateCalendarIntegrationRequest;
use App\Http\Resources\CalendarIntegrationResource;
use App\Http\Resources\ExternalCalendarEventResource;
use App\Models\CalendarIntegration;
use App\Models\ExternalCalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CalendarIntegrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CalendarIntegrationResource::collection(
            CalendarIntegration::query()->latest()->paginate(15)
        );
    }

    public function store(StoreCalendarIntegrationRequest $request): JsonResponse
    {
        $integration = CalendarIntegration::create(array_merge(
            $request->validated(),
            [
                'user_id' => $request->user()->id,
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new CalendarIntegrationResource($integration))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CalendarIntegration $calendarIntegration): CalendarIntegrationResource
    {
        $this->authorize('view', $calendarIntegration);

        return new CalendarIntegrationResource($calendarIntegration);
    }

    public function update(UpdateCalendarIntegrationRequest $request, CalendarIntegration $calendarIntegration): CalendarIntegrationResource
    {
        $calendarIntegration->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new CalendarIntegrationResource($calendarIntegration->fresh());
    }

    public function destroy(CalendarIntegration $calendarIntegration): JsonResponse
    {
        $this->authorize('delete', $calendarIntegration);

        $calendarIntegration->delete();

        return response()->json(null, 204);
    }

    /**
     * Upsert calendar events — prevents duplicates via provider_event_id unique constraint.
     */
    public function upsertEvents(Request $request, CalendarIntegration $calendarIntegration): JsonResponse
    {
        $this->authorize('view', $calendarIntegration);

        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.provider_event_id' => 'required|string|max:255',
            'events.*.title' => 'required|string|max:500',
            'events.*.description' => 'nullable|string',
            'events.*.starts_at' => 'required|date',
            'events.*.ends_at' => 'required|date|after_or_equal:events.*.starts_at',
            'events.*.timezone' => 'nullable|string|max:50',
            'events.*.is_all_day' => 'sometimes|boolean',
            'events.*.attendees' => 'nullable|array',
            'events.*.location' => 'nullable|string|max:500',
            'events.*.linked_matter_id' => 'nullable|exists:matters,id',
            'events.*.linked_hearing_id' => 'nullable|exists:hearings,id',
        ]);

        $upserted = [];
        foreach ($validated['events'] as $eventData) {
            $event = ExternalCalendarEvent::updateOrCreate(
                [
                    'calendar_integration_id' => $calendarIntegration->id,
                    'provider_event_id' => $eventData['provider_event_id'],
                ],
                array_merge($eventData, [
                    'workspace_id' => $calendarIntegration->workspace_id,
                    'user_id' => $request->user()->id,
                    'calendar_integration_id' => $calendarIntegration->id,
                    'last_synced_at' => now(),
                ])
            );
            $upserted[] = $event;
        }

        return ExternalCalendarEventResource::collection(collect($upserted))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * List events for an integration.
     */
    public function events(CalendarIntegration $calendarIntegration): AnonymousResourceCollection
    {
        $this->authorize('view', $calendarIntegration);

        return ExternalCalendarEventResource::collection(
            $calendarIntegration->events()->latest('starts_at')->paginate(15)
        );
    }
}
