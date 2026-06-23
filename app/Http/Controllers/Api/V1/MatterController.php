<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MatterTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMatterRequest;
use App\Http\Requests\UpdateMatterRequest;
use App\Http\Resources\MatterResource;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MatterController extends Controller
{
    public function types(): JsonResponse
    {
        $grouped = [
            'transactional' => [],
            'litigation' => [],
        ];

        foreach (MatterTypeEnum::cases() as $case) {
            $grouped[$case->track()][] = [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        }

        return response()->json(['data' => $grouped]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Matter::with(['client', 'leadLawyer']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('practice_area')) {
            $query->where('practice_area', $request->input('practice_area'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%'.$request->input('search').'%');
        }

        return MatterResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreMatterRequest $request): JsonResponse
    {
        $matter = Matter::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new MatterResource($matter->load(['client', 'leadLawyer'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Matter $matter): MatterResource
    {
        $this->authorize('view', $matter);

        return new MatterResource(
            $matter->load(['client', 'counterparties', 'leadLawyer', 'lawyers'])
        );
    }

    public function update(UpdateMatterRequest $request, Matter $matter): MatterResource
    {
        $matter->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new MatterResource($matter->fresh(['client', 'leadLawyer']));
    }

    public function destroy(Matter $matter): JsonResponse
    {
        $this->authorize('delete', $matter);

        $matter->delete();

        return response()->json(null, 204);
    }

    public function attachCounterparty(Request $request, Matter $matter): JsonResponse
    {
        $this->authorize('update', $matter);

        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'representing' => 'sometimes|in:we_represent,they_represent,no_counsel',
        ]);

        if ($matter->client_id === $request->input('contact_id')) {
            return response()->json([
                'message' => __('matters.cannot_attach_client_as_counterparty'),
            ], 422);
        }

        $matter->counterparties()->syncWithoutDetaching([
            $request->input('contact_id') => [
                'representing' => $request->input('representing', 'no_counsel'),
            ],
        ]);

        return response()->json(null, 201);
    }

    public function detachCounterparty(Matter $matter, Contact $contact): JsonResponse
    {
        $this->authorize('update', $matter);

        $matter->counterparties()->detach($contact->id);

        return response()->json(null, 204);
    }

    public function attachLawyer(Request $request, Matter $matter): JsonResponse
    {
        $this->authorize('update', $matter);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:50',
        ]);

        $matter->lawyers()->syncWithoutDetaching([
            $request->input('user_id') => [
                'role' => $request->input('role'),
            ],
        ]);

        return response()->json(null, 201);
    }

    public function detachLawyer(Matter $matter, User $user): JsonResponse
    {
        $this->authorize('update', $matter);

        $matter->lawyers()->detach($user->id);

        return response()->json(null, 204);
    }
}
