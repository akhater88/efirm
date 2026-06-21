<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::query();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('is_client')) {
            $query->where('is_client', filter_var($request->input('is_client'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_counterparty')) {
            $query->where('is_counterparty', filter_var($request->input('is_counterparty'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('display_name', 'LIKE', "%{$search}%");
        }

        return ContactResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create(array_merge(
            $request->validated(),
            [
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]
        ));

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Contact $contact): ContactResource
    {
        $this->authorize('view', $contact);

        return new ContactResource($contact->load('parentOrganization'));
    }

    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new ContactResource($contact->fresh());
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        return response()->json(null, 204);
    }
}
