<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\Matter;
use App\Services\DocumentTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DocumentTemplate::class);

        $query = DocumentTemplate::query();

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Include global templates (workspace_id=null) alongside workspace-specific ones
        $query->where(function ($q) {
            $q->whereNull('workspace_id')
                ->orWhereNotNull('workspace_id');
        });

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', DocumentTemplate::class);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string|max:100',
            'language' => 'nullable|string|in:arabic,english,bilingual',
            'body' => 'required|array',
            'placeholder_schema' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $template = DocumentTemplate::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'description' => $validated['description'] ?? null,
            'document_type' => $validated['document_type'],
            'language' => $validated['language'] ?? 'bilingual',
            'body' => $validated['body'],
            'placeholder_schema' => $validated['placeholder_schema'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $template,
        ], 201);
    }

    public function show(DocumentTemplate $documentTemplate): JsonResponse
    {
        $this->authorize('view', $documentTemplate);

        return response()->json([
            'data' => $documentTemplate,
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate): JsonResponse
    {
        $this->authorize('update', $documentTemplate);

        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'sometimes|string|max:100',
            'language' => 'nullable|string|in:arabic,english,bilingual',
            'body' => 'sometimes|array',
            'placeholder_schema' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $documentTemplate->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json([
            'data' => $documentTemplate->fresh(),
        ]);
    }

    public function destroy(DocumentTemplate $documentTemplate): JsonResponse
    {
        $this->authorize('delete', $documentTemplate);

        $documentTemplate->delete();

        return response()->json(null, 204);
    }

    public function createFromTemplate(Request $request, Matter $matter, DocumentTemplateService $service): JsonResponse
    {
        $this->authorize('update', $matter);

        $validated = $request->validate([
            'document_template_id' => 'required|string|size:26',
            'replacements' => 'nullable|array',
            'title' => 'nullable|string|max:255',
        ]);

        $template = DocumentTemplate::findOrFail($validated['document_template_id']);

        $document = $service->createFromTemplate(
            $template,
            $matter,
            $request->user(),
            $validated['replacements'] ?? [],
            $validated['title'] ?? null,
        );

        return response()->json([
            'data' => $document->load('currentVersion'),
        ], 201);
    }
}
