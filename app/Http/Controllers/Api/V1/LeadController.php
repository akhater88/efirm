<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\LeadStatus;
use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Http\Resources\OpportunityResource;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lead::query()->with(['contact', 'pipeline']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('pipeline_id')) {
            $query->where('pipeline_id', $request->input('pipeline_id'));
        }

        return LeadResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new LeadResource($lead->fresh()->load(['contact', 'pipeline'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);

        return new LeadResource($lead->load(['contact', 'pipeline']));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $lead->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new LeadResource($lead->fresh()->load(['contact', 'pipeline']));
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->json(null, 204);
    }

    /**
     * Convert a lead to an opportunity.
     */
    public function convert(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('convert', $lead);

        if ($lead->status === LeadStatus::Converted) {
            return response()->json(['message' => __('crm.lead_already_converted')], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'expected_close_date' => 'nullable|date',
        ]);

        $opportunity = DB::transaction(function () use ($lead, $validated, $request) {
            $opportunity = Opportunity::create([
                'workspace_id' => $lead->workspace_id,
                'contact_id' => $lead->contact_id,
                'pipeline_id' => $lead->pipeline_id,
                'lead_id' => $lead->id,
                'title' => $validated['title'] ?? $lead->title,
                'status' => OpportunityStatus::Open,
                'current_stage' => $lead->current_stage,
                'estimated_value' => $validated['estimated_value'] ?? null,
                'currency' => $validated['currency'] ?? 'USD',
                'expected_close_date' => $validated['expected_close_date'] ?? null,
                'assigned_to_user_id' => $lead->assigned_to_user_id,
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            $lead->update([
                'status' => LeadStatus::Converted,
                'converted_to_opportunity_id' => $opportunity->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            return $opportunity;
        });

        return (new OpportunityResource($opportunity->load(['contact', 'pipeline'])))
            ->response()
            ->setStatusCode(201);
    }
}
