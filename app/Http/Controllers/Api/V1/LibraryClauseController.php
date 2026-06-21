<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\LibraryClause;
use App\Services\LibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryClauseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LibraryClause::with(['createdBy', 'fallbackOf']);

        if ($request->filled('clause_type')) {
            $query->where('clause_type', $request->input('clause_type'));
        }

        if ($request->filled('practice_area')) {
            $query->where('practice_area', $request->input('practice_area'));
        }

        if ($request->filled('risk_position')) {
            $query->where('risk_position', $request->input('risk_position'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'LIKE', "%{$search}%");
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LibraryClause::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'clause_type' => 'nullable|string|max:100',
            'practice_area' => 'nullable|string',
            'language' => 'nullable|string|in:ar,en,mixed',
            'body_ar' => 'nullable|array',
            'body_en' => 'nullable|array',
            'risk_position' => 'nullable|string|in:favourable,balanced,adverse',
            'is_fallback_of_id' => 'nullable|string|size:26',
            'tags' => 'nullable|array',
        ]);

        $clause = LibraryClause::create(array_merge($validated, [
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $clause], 201);
    }

    public function show(LibraryClause $libraryClause): JsonResponse
    {
        $this->authorize('view', $libraryClause);

        return response()->json([
            'data' => $libraryClause->load(['createdBy', 'fallbackOf', 'fallbacks']),
        ]);
    }

    public function update(Request $request, LibraryClause $libraryClause): JsonResponse
    {
        $this->authorize('update', $libraryClause);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'clause_type' => 'nullable|string|max:100',
            'practice_area' => 'nullable|string',
            'language' => 'nullable|string|in:ar,en,mixed',
            'body_ar' => 'nullable|array',
            'body_en' => 'nullable|array',
            'risk_position' => 'nullable|string|in:favourable,balanced,adverse',
            'is_fallback_of_id' => 'nullable|string|size:26',
            'tags' => 'nullable|array',
        ]);

        $libraryClause->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $libraryClause->fresh()]);
    }

    public function destroy(LibraryClause $libraryClause): JsonResponse
    {
        $this->authorize('delete', $libraryClause);

        $libraryClause->delete();

        return response()->json(null, 204);
    }

    public function saveFromDocumentClause(Request $request, DocumentClause $documentClause, LibraryService $libraryService): JsonResponse
    {
        $this->authorize('create', LibraryClause::class);

        $attrs = $request->validate([
            'title' => 'nullable|string|max:255',
            'clause_type' => 'nullable|string|max:100',
            'risk_position' => 'nullable|string|in:favourable,balanced,adverse',
            'tags' => 'nullable|array',
        ]);

        $clause = $libraryService->saveFromDocument($documentClause, $request->user(), $attrs);

        return response()->json(['data' => $clause], 201);
    }

    public function insertIntoDocument(Request $request, Document $document, LibraryService $libraryService): JsonResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'library_clause_id' => 'required|string|size:26',
            'position' => 'nullable|integer|min:0',
        ]);

        $libClause = LibraryClause::findOrFail($validated['library_clause_id']);
        $currentVersion = $document->currentVersion;

        if (! $currentVersion) {
            return response()->json(['error' => 'Document has no current version'], 422);
        }

        $newVersion = $libraryService->insertIntoDocument(
            $libClause,
            $currentVersion,
            $request->user(),
            $validated['position'] ?? null,
        );

        return response()->json([
            'data' => [
                'version_id' => $newVersion->id,
                'version_number' => $newVersion->version_number,
                'library_clause_id' => $libClause->id,
                'usage_count' => $libClause->fresh()->usage_count,
            ],
        ], 201);
    }
}
