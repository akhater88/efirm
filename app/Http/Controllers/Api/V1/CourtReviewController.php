<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourtReviewRequest;
use App\Http\Requests\UpdateCourtReviewRequest;
use App\Http\Resources\CourtReviewResource;
use App\Models\CourtReview;
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
}
