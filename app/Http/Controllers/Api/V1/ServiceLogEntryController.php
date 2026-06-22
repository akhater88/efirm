<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceLogEntryRequest;
use App\Http\Requests\UpdateServiceLogEntryRequest;
use App\Http\Resources\ServiceLogEntryResource;
use App\Models\ServiceLogEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceLogEntryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ServiceLogEntry::with('servedParty');

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->input('matter_id'));
        }

        return ServiceLogEntryResource::collection($query->latest('service_date')->paginate(15));
    }

    public function store(StoreServiceLogEntryRequest $request): JsonResponse
    {
        $entry = ServiceLogEntry::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new ServiceLogEntryResource($entry->load('servedParty')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ServiceLogEntry $serviceLogEntry): ServiceLogEntryResource
    {
        $this->authorize('view', $serviceLogEntry);

        return new ServiceLogEntryResource($serviceLogEntry->load(['servedParty', 'matter']));
    }

    public function update(UpdateServiceLogEntryRequest $request, ServiceLogEntry $serviceLogEntry): ServiceLogEntryResource
    {
        $serviceLogEntry->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new ServiceLogEntryResource($serviceLogEntry->fresh('servedParty'));
    }

    public function destroy(ServiceLogEntry $serviceLogEntry): JsonResponse
    {
        $this->authorize('delete', $serviceLogEntry);

        $serviceLogEntry->delete();

        return response()->json(null, 204);
    }
}
