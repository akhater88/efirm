<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrustAccountRequest;
use App\Http\Requests\UpdateTrustAccountRequest;
use App\Http\Resources\TrustAccountResource;
use App\Http\Resources\TrustLedgerEntryResource;
use App\Models\TrustAccount;
use App\Services\TrustAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrustAccountController extends Controller
{
    public function __construct(
        private readonly TrustAccountService $trustAccountService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TrustAccount::query()->with('contact');

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->input('contact_id'));
        }

        return TrustAccountResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreTrustAccountRequest $request): JsonResponse
    {
        $trustAccount = TrustAccount::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new TrustAccountResource($trustAccount->fresh()->load('contact')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TrustAccount $trustAccount): TrustAccountResource
    {
        $this->authorize('view', $trustAccount);

        return new TrustAccountResource($trustAccount->load(['contact', 'ledgerEntries']));
    }

    public function update(UpdateTrustAccountRequest $request, TrustAccount $trustAccount): TrustAccountResource
    {
        $trustAccount->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new TrustAccountResource($trustAccount->fresh()->load('contact'));
    }

    public function destroy(TrustAccount $trustAccount): JsonResponse
    {
        $this->authorize('delete', $trustAccount);

        $trustAccount->delete();

        return response()->json(null, 204);
    }

    public function deposit(Request $request, TrustAccount $trustAccount): JsonResponse
    {
        $this->authorize('deposit', $trustAccount);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $entry = $this->trustAccountService->deposit(
            $trustAccount,
            number_format((float) $validated['amount'], 2, '.', ''),
            $validated['description'] ?? null,
            $validated['reference'] ?? null,
            $request->user()->id,
        );

        return (new TrustLedgerEntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    public function withdraw(Request $request, TrustAccount $trustAccount): JsonResponse
    {
        $this->authorize('withdraw', $trustAccount);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
        ]);

        try {
            $entry = $this->trustAccountService->withdraw(
                $trustAccount,
                number_format((float) $validated['amount'], 2, '.', ''),
                $validated['description'] ?? null,
                $validated['reference'] ?? null,
                $request->user()->id,
            );

            return (new TrustLedgerEntryResource($entry))
                ->response()
                ->setStatusCode(201);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
