<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\HearingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHearingRequest;
use App\Http\Requests\UpdateHearingRequest;
use App\Http\Resources\HearingResource;
use App\Models\Hearing;
use App\Models\Matter;
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

    public function update(UpdateHearingRequest $request, Hearing $hearing): HearingResource
    {
        $hearing->update(array_merge(
            $request->validated(),
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
