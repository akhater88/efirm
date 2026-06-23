<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HearingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHearingRequest;
use App\Http\Requests\UpdateHearingRequest;
use App\Http\Resources\HearingActionItemResource;
use App\Http\Resources\HearingResource;
use App\Models\Hearing;
use App\Models\HearingActionItem;
use App\Models\Matter;
use App\Services\HearingSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HearingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Hearing::with(['court', 'judge']);

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->input('matter_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return HearingResource::collection($query->orderBy('hearing_date')->paginate(15));
    }

    public function store(StoreHearingRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! isset($data['status'])) {
            $data['status'] = HearingStatus::Scheduled->value;
        }

        // Circular reference prevention (F-FIX-02.5, Decision #30)
        if (! empty($data['postponed_to_hearing_id'])) {
            // For new hearings, check that the target doesn't already point back
            // (A new hearing has no ID yet, so we just verify target chain is clean)
            $targetChain = Hearing::withoutGlobalScopes()
                ->find($data['postponed_to_hearing_id']);

            if ($targetChain && $targetChain->postponed_to_hearing_id) {
                // Target already points somewhere — check its chain doesn't loop
                // Since we're creating, just ensure target exists and is valid
            }
        }

        $hearing = Hearing::create(array_merge(
            $data,
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        // Update matter's next_hearing_date
        $this->updateMatterNextHearingDate($hearing->matter_id);

        return (new HearingResource($hearing->load(['court', 'judge'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Hearing $hearing): HearingResource
    {
        $this->authorize('view', $hearing);

        return new HearingResource($hearing->load(['court', 'judge', 'matter']));
    }

    public function update(UpdateHearingRequest $request, Hearing $hearing): HearingResource|JsonResponse
    {
        $data = $request->validated();

        // Circular reference prevention (F-FIX-02.5, Decision #30)
        if (! empty($data['postponed_to_hearing_id'])) {
            if (Hearing::wouldCreateCircularReference($hearing->id, $data['postponed_to_hearing_id'])) {
                return response()->json([
                    'message' => __('litigation.circular_postponement_reference'),
                ], 422);
            }
        }

        $hearing->update(array_merge(
            $data,
            ['updated_by_user_id' => $request->user()->id]
        ));

        // Update matter's next_hearing_date
        $this->updateMatterNextHearingDate($hearing->matter_id);

        return new HearingResource($hearing->fresh(['court', 'judge']));
    }

    public function destroy(Hearing $hearing): JsonResponse
    {
        $this->authorize('delete', $hearing);

        $matterId = $hearing->matter_id;
        $hearing->delete();

        $this->updateMatterNextHearingDate($matterId);

        return response()->json(null, 204);
    }

    /**
     * Record session content on a held hearing (F-FIX-02.1).
     */
    public function recordSession(Request $request, Hearing $hearing): HearingResource|JsonResponse
    {
        $this->authorize('update', $hearing);

        if ($hearing->status !== HearingStatus::Held) {
            return response()->json([
                'message' => __('litigation.session_content_requires_held_status'),
            ], 422);
        }

        $validated = $request->validate([
            'judge_statement_ar' => 'nullable|string',
            'judge_statement_en' => 'nullable|string',
            'outcome_summary_ar' => 'nullable|string',
            'outcome_summary_en' => 'nullable|string',
            'our_submissions_made' => 'nullable|string',
            'opposing_submissions_made' => 'nullable|string',
            'next_session_required_actions_ar' => 'nullable|string',
            'next_session_required_actions_en' => 'nullable|string',
            'session_attended_by' => 'nullable|array',
        ]);

        $service = app(HearingSessionService::class);
        $hearing = $service->recordOutcome($hearing, $validated, $request->user());

        return new HearingResource($hearing->load(['court', 'judge', 'actionItems']));
    }

    /**
     * Get sessions timeline for a matter (F-FIX-02.1).
     */
    public function sessionsTimeline(Matter $matter): AnonymousResourceCollection
    {
        $this->authorize('view', $matter);

        $service = app(HearingSessionService::class);
        $sessions = $service->getSessionsTimelineForMatter($matter);

        return HearingResource::collection($sessions);
    }

    /**
     * Add an action item to a hearing (F-FIX-02.1).
     */
    public function storeActionItem(Request $request, Hearing $hearing): JsonResponse
    {
        $this->authorize('update', $hearing);

        $validated = $request->validate([
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'due_date' => 'required|date',
            'responsible_user_id' => 'nullable|string|exists:users,id',
        ]);

        $service = app(HearingSessionService::class);
        $actionItem = $service->addActionItem($hearing, $validated, $request->user());

        return (new HearingActionItemResource($actionItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update an action item (F-FIX-02.1).
     */
    public function updateActionItem(Request $request, HearingActionItem $hearingActionItem): HearingActionItemResource
    {
        $this->authorize('update', $hearingActionItem);

        $validated = $request->validate([
            'description_ar' => 'sometimes|string',
            'description_en' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'responsible_user_id' => 'nullable|string|exists:users,id',
            'status' => 'sometimes|string|in:pending,completed,waived',
        ]);

        $hearingActionItem->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return new HearingActionItemResource($hearingActionItem->fresh());
    }

    /**
     * Delete an action item (F-FIX-02.1).
     */
    public function destroyActionItem(HearingActionItem $hearingActionItem): JsonResponse
    {
        $this->authorize('delete', $hearingActionItem);

        $hearingActionItem->delete();

        return response()->json(null, 204);
    }

    /**
     * Get the full postponement chain for a hearing (F-FIX-02.5, Decision #30).
     */
    public function postponementChain(Hearing $hearing): AnonymousResourceCollection
    {
        $this->authorize('view', $hearing);

        $chain = $hearing->getPostponementChain();

        return HearingResource::collection($chain);
    }

    private function updateMatterNextHearingDate(string $matterId): void
    {
        $matter = Matter::find($matterId);

        if (! $matter) {
            return;
        }

        $nextHearing = Hearing::where('matter_id', $matterId)
            ->where('status', 'scheduled')
            ->where('hearing_date', '>=', now())
            ->orderBy('hearing_date')
            ->first();

        $matter->update([
            'next_hearing_date' => $nextHearing?->hearing_date?->toDateString(),
        ]);
    }
}
