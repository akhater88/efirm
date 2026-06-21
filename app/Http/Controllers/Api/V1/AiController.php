<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiInteraction;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Services\AiOrchestrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        private AiOrchestrationService $aiService,
    ) {}

    public function draft(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'intent' => 'required|string|max:1000',
            'language' => 'required|string|in:ar,en',
        ]);

        $interaction = $this->aiService->draft(
            $document,
            $validated['intent'],
            $validated['language'],
            $request->user(),
        );

        return response()->json(['data' => $this->formatInteraction($interaction)], 201);
    }

    public function review(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'clause_id' => 'required|string|size:26',
        ]);

        $clause = DocumentClause::where('id', $validated['clause_id'])->firstOrFail();

        $interaction = $this->aiService->review($clause, $request->user());

        return response()->json(['data' => $this->formatInteraction($interaction)], 201);
    }

    public function suggest(Request $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'clause_id' => 'required|string|size:26',
            'instruction' => 'required|string|max:1000',
        ]);

        $clause = DocumentClause::where('id', $validated['clause_id'])->firstOrFail();

        $interaction = $this->aiService->suggest(
            $clause,
            $validated['instruction'],
            $request->user(),
        );

        return response()->json(['data' => $this->formatInteraction($interaction)], 201);
    }

    public function translate(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'clause_id' => 'required|string|size:26',
            'target_language' => 'required|string|in:ar,en',
        ]);

        $clause = DocumentClause::where('id', $validated['clause_id'])->firstOrFail();

        $interaction = $this->aiService->translate(
            $clause,
            $validated['target_language'],
            $request->user(),
        );

        return response()->json(['data' => $this->formatInteraction($interaction)], 201);
    }

    public function explain(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'clause_id' => 'required|string|size:26',
        ]);

        $clause = DocumentClause::where('id', $validated['clause_id'])->firstOrFail();

        $interaction = $this->aiService->explain($clause, $request->user());

        return response()->json(['data' => $this->formatInteraction($interaction)], 201);
    }

    public function accept(Request $request, AiInteraction $aiInteraction): JsonResponse
    {
        $this->aiService->markAccepted($aiInteraction, true);

        return response()->json(['data' => ['was_accepted' => true]]);
    }

    public function reject(Request $request, AiInteraction $aiInteraction): JsonResponse
    {
        $this->aiService->markAccepted($aiInteraction, false);

        return response()->json(['data' => ['was_accepted' => false]]);
    }

    private function formatInteraction(AiInteraction $interaction): array
    {
        return [
            'id' => $interaction->id,
            'interaction_type' => $interaction->interaction_type->value,
            'response' => $interaction->response,
            'model' => $interaction->model,
            'input_tokens' => $interaction->input_tokens,
            'output_tokens' => $interaction->output_tokens,
            'cost_usd' => $interaction->cost_usd,
            'latency_ms' => $interaction->latency_ms,
        ];
    }
}
