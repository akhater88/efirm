<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpertReportRequest;
use App\Http\Requests\UpdateExpertReportRequest;
use App\Http\Resources\ExpertReportResource;
use App\Models\ExpertReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * API controller for Expert Report CRUD.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */
class ExpertReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ExpertReport::with(['matter']);

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->input('matter_id'));
        }

        return ExpertReportResource::collection($query->orderByDesc('received_date')->paginate(15));
    }

    public function store(StoreExpertReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        $expertReport = ExpertReport::create(array_merge(
            $data,
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new ExpertReportResource($expertReport->fresh()->load('matter')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ExpertReport $expertReport): ExpertReportResource
    {
        $this->authorize('view', $expertReport);

        return new ExpertReportResource($expertReport->load(['matter', 'document']));
    }

    public function update(UpdateExpertReportRequest $request, ExpertReport $expertReport): ExpertReportResource
    {
        $expertReport->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new ExpertReportResource($expertReport->fresh(['matter']));
    }

    public function destroy(ExpertReport $expertReport): JsonResponse
    {
        $this->authorize('delete', $expertReport);

        $expertReport->delete();

        return response()->json(null, 204);
    }
}
