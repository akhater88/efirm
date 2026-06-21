<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContractMetadata;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractMetadataController extends Controller
{
    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $metadata = $document->contractMetadata;

        if (! $metadata) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $metadata]);
    }

    public function upsert(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'contract_value' => 'nullable|numeric|min:0',
            'contract_currency' => 'nullable|string|size:3',
            'effective_date' => 'nullable|date',
            'term_months' => 'nullable|integer|min:1|max:600',
            'expiry_date' => 'nullable|date',
            'auto_renew' => 'nullable|boolean',
            'renewal_notice_period_days' => 'nullable|integer|min:0|max:365',
            'governing_law' => 'nullable|string|max:100',
            'jurisdiction_clause' => 'nullable|string|max:255',
            'signed_date' => 'nullable|date',
        ]);

        $metadata = ContractMetadata::updateOrCreate(
            ['document_id' => $document->id],
            array_merge($validated, [
                'workspace_id' => $document->workspace_id,
                'updated_by_user_id' => $request->user()->id,
            ]),
        );

        if ($metadata->wasRecentlyCreated) {
            $metadata->update(['created_by_user_id' => $request->user()->id]);
        }

        return response()->json(['data' => $metadata->fresh()]);
    }
}
