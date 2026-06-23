<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtReviewRequest;
use App\Http\Requests\UpdateCourtReviewRequest;
use App\Http\Resources\CourtReviewResource;
use App\Models\CourtReview;
use App\Models\User;
use App\Services\CourtReviewDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourtReviewController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CourtReview::with('hearing');

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->input('matter_id'));
        }

        return CourtReviewResource::collection($query->latest('decision_date')->paginate(15));
    }

    public function store(StoreCourtReviewRequest $request): JsonResponse
    {
        $courtReview = CourtReview::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new CourtReviewResource($courtReview->load('hearing')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CourtReview $courtReview): CourtReviewResource
    {
        $this->authorize('view', $courtReview);

        return new CourtReviewResource($courtReview->load(['hearing', 'matter']));
    }

    public function update(UpdateCourtReviewRequest $request, CourtReview $courtReview): CourtReviewResource
    {
        $courtReview->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new CourtReviewResource($courtReview->fresh('hearing'));
    }

    public function destroy(CourtReview $courtReview): JsonResponse
    {
        $this->authorize('delete', $courtReview);

        $courtReview->delete();

        return response()->json(null, 204);
    }

    /**
     * Dispatch a court review to a trainee (F-FIX-02.2).
     */
    public function dispatch(Request $request, CourtReview $courtReview): CourtReviewResource
    {
        $this->authorize('update', $courtReview);

        $validated = $request->validate([
            'dispatched_to_user_id' => 'required|string|exists:users,id',
            'location_in_courthouse_ar' => 'nullable|string|max:200',
            'location_in_courthouse_en' => 'nullable|string|max:200',
            'expected_outcome_ar' => 'nullable|string',
            'expected_outcome_en' => 'nullable|string',
        ]);

        $assignTo = User::findOrFail($validated['dispatched_to_user_id']);
        unset($validated['dispatched_to_user_id']);

        $service = app(CourtReviewDispatchService::class);
        $courtReview = $service->dispatch($courtReview, $assignTo, $validated, $request->user());

        return new CourtReviewResource($courtReview->load('hearing'));
    }

    /**
     * Complete a dispatched court review (F-FIX-02.2).
     */
    public function complete(Request $request, CourtReview $courtReview): CourtReviewResource
    {
        $this->authorize('update', $courtReview);

        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'evidence_document_id' => 'nullable|string|exists:documents,id',
        ]);

        $service = app(CourtReviewDispatchService::class);
        $courtReview = $service->complete($courtReview, $validated, $request->user());

        return new CourtReviewResource($courtReview->load('hearing'));
    }

    /**
     * Get court reviews dispatched to the authenticated user (F-FIX-02.2).
     */
    public function dispatchedToMe(Request $request): AnonymousResourceCollection
    {
        $service = app(CourtReviewDispatchService::class);
        $reviews = $service->getDispatchedToMe($request->user());

        return CourtReviewResource::collection($reviews);
    }
}
