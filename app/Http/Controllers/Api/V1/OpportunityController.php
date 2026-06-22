<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MatterStatus;
use App\Enums\OpportunityStatus;
use App\Enums\PracticeArea;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\MatterResource;
use App\Http\Resources\OpportunityResource;
use App\Models\Matter;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OpportunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Opportunity::query()->with(['contact', 'pipeline']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('pipeline_id')) {
            $query->where('pipeline_id', $request->input('pipeline_id'));
        }

        return OpportunityResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreOpportunityRequest $request): JsonResponse
    {
        $opportunity = Opportunity::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new OpportunityResource($opportunity->fresh()->load(['contact', 'pipeline'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Opportunity $opportunity): OpportunityResource
    {
        $this->authorize('view', $opportunity);

        return new OpportunityResource($opportunity->load(['contact', 'pipeline']));
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): OpportunityResource
    {
        $opportunity->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new OpportunityResource($opportunity->fresh()->load(['contact', 'pipeline']));
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('delete', $opportunity);

        $opportunity->delete();

        return response()->json(null, 204);
    }

    /**
     * Convert an opportunity to a matter.
     */
    public function convert(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('convert', $opportunity);

        if ($opportunity->status === OpportunityStatus::Won && $opportunity->converted_to_matter_id) {
            return response()->json(['message' => __('crm.opportunity_already_converted')], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'practice_area' => 'sometimes|string',
        ]);

        $matter = DB::transaction(function () use ($opportunity, $validated, $request) {
            $matter = Matter::create([
                'workspace_id' => $opportunity->workspace_id,
                'title' => $validated['title'] ?? $opportunity->title,
                'client_id' => $opportunity->contact_id,
                'practice_area' => $validated['practice_area'] ?? PracticeArea::CommercialContracts->value,
                'status' => MatterStatus::Active,
                'opened_at' => now(),
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            $opportunity->update([
                'status' => OpportunityStatus::Won,
                'converted_to_matter_id' => $matter->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            return $matter;
        });

        return (new MatterResource($matter))
            ->response()
            ->setStatusCode(201);
    }
}
