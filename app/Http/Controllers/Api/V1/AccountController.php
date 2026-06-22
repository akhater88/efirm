<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Account::query()->with('children');

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->input('account_type'));
        }

        if ($request->has('is_system')) {
            $query->where('is_system', filter_var($request->input('is_system'), FILTER_VALIDATE_BOOLEAN));
        }

        // Return tree: only root accounts (no parent)
        if ($request->boolean('tree')) {
            $query->whereNull('parent_id');
        }

        return AccountResource::collection(
            $query->orderBy('code')->paginate(50)
        );
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Account $account): AccountResource
    {
        $this->authorize('view', $account);

        return new AccountResource($account->load('children'));
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new AccountResource($account->fresh());
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return response()->json(null, 204);
    }
}
