<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Obligation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ObligationController extends Controller
{
    public function index(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $obligations = $document->obligations()
            ->with(['responsibleUser', 'createdBy'])
            ->orderBy('due_date')
            ->paginate(20);

        return response()->json(['data' => $obligations]);
    }

    public function store(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);
        $this->authorize('create', Obligation::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'obligation_type' => 'required|string|in:payment,delivery,reporting,notification,consent,other',
            'responsible_party' => 'required|string|in:us,counterparty,mutual,third_party',
            'responsible_user_id' => 'nullable|string|size:26',
            'due_date' => 'required|date',
            'reminder_days_before' => 'nullable|integer|min:0|max:365',
            'monetary_amount' => 'nullable|numeric|min:0',
            'monetary_currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $obligation = Obligation::create(array_merge($validated, [
            'workspace_id' => $document->workspace_id,
            'document_id' => $document->id,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $obligation], 201);
    }

    public function show(Obligation $obligation): JsonResponse
    {
        $this->authorize('view', $obligation);

        return response()->json([
            'data' => $obligation->load(['document', 'responsibleUser', 'createdBy']),
        ]);
    }

    public function update(Request $request, Obligation $obligation): JsonResponse
    {
        $this->authorize('update', $obligation);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'obligation_type' => 'sometimes|string|in:payment,delivery,reporting,notification,consent,other',
            'responsible_party' => 'sometimes|string|in:us,counterparty,mutual,third_party',
            'responsible_user_id' => 'nullable|string|size:26',
            'due_date' => 'sometimes|date',
            'reminder_days_before' => 'nullable|integer|min:0|max:365',
            'status' => 'sometimes|string|in:pending,in_progress,completed,overdue,waived',
            'monetary_amount' => 'nullable|numeric|min:0',
            'monetary_currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $obligation->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $obligation->fresh()]);
    }

    public function complete(Request $request, Obligation $obligation): JsonResponse
    {
        $this->authorize('update', $obligation);

        $obligation->markComplete($request->user());

        return response()->json(['data' => $obligation->fresh()]);
    }

    public function destroy(Obligation $obligation): JsonResponse
    {
        $this->authorize('delete', $obligation);

        $obligation->delete();

        return response()->json(null, 204);
    }
}
