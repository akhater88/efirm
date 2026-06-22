<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AiGenerationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiGenerationTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workspace = $request->user()->currentWorkspace();

        $templates = AiGenerationTemplate::forWorkspace($workspace?->id)
            ->active()
            ->orderBy('key')
            ->get();

        return response()->json(['data' => $templates]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'name_ar' => 'required|string|max:200',
            'name_en' => 'required|string|max:200',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'prompt_template' => 'required|string',
            'intent_schema' => 'nullable|array',
        ]);

        $template = AiGenerationTemplate::create(array_merge($validated, [
            'workspace_id' => $request->user()->currentWorkspace()?->id,
            'legal_review_status' => 'pending',
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $template], 201);
    }

    public function show(AiGenerationTemplate $aiGenerationTemplate): JsonResponse
    {
        return response()->json(['data' => $aiGenerationTemplate]);
    }

    public function update(Request $request, AiGenerationTemplate $aiGenerationTemplate): JsonResponse
    {
        if ($aiGenerationTemplate->isSystemTemplate()) {
            return response()->json(['error' => 'Cannot edit system templates. Clone instead.'], 422);
        }

        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:200',
            'name_en' => 'sometimes|string|max:200',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'prompt_template' => 'sometimes|string',
            'intent_schema' => 'nullable|array',
            'legal_review_status' => 'sometimes|string|in:pending,approved,revoked',
            'legal_review_approver_name' => 'nullable|string|max:200',
            'is_active' => 'sometimes|boolean',
        ]);

        // Version increment on prompt change
        if (isset($validated['prompt_template']) && $validated['prompt_template'] !== $aiGenerationTemplate->prompt_template) {
            $validated['version'] = $aiGenerationTemplate->version + 1;
        }

        $aiGenerationTemplate->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $aiGenerationTemplate->fresh()]);
    }

    public function destroy(AiGenerationTemplate $aiGenerationTemplate): JsonResponse
    {
        if ($aiGenerationTemplate->isSystemTemplate()) {
            return response()->json(['error' => 'Cannot delete system templates.'], 422);
        }

        $aiGenerationTemplate->delete();

        return response()->json(null, 204);
    }
}
