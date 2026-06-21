<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\KycChecklist;
use App\Models\KycItem;
use App\Services\KycService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('view', $contact);

        $checklist = KycChecklist::where('contact_id', $contact->id)
            ->with('items')
            ->latest()
            ->first();

        return response()->json(['data' => $checklist]);
    }

    public function start(Request $request, Contact $contact, KycService $kycService): JsonResponse
    {
        $this->authorize('update', $contact);

        // Check if already has an active checklist
        $existing = KycChecklist::where('contact_id', $contact->id)
            ->whereNotIn('status', ['complete', 'expired'])
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'active_checklist_exists',
                'data' => $existing->load('items'),
            ], 409);
        }

        $checklist = $kycService->start($contact, $request->user());

        return response()->json(['data' => $checklist], 201);
    }

    public function updateItem(Request $request, KycItem $kycItem): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:not_requested,requested,received,verified,rejected,expired',
            'expiry_date' => 'nullable|date',
            'document_id' => 'nullable|string|size:26',
            'notes' => 'nullable|string',
        ]);

        $kycItem->update($validated);

        // Recalculate checklist status
        $kycItem->checklist->recalculateStatus();

        return response()->json(['data' => $kycItem->fresh()]);
    }
}
