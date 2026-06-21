<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportDocumentRequest;
use App\Http\Requests\SaveDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentVersionResource;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Services\DocumentImportService;
use App\Services\DocumentService;
use App\Services\VersionDiffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentController extends Controller
{
    public function index(Matter $matter, Request $request): AnonymousResourceCollection
    {
        $this->authorize('view', $matter);

        $documents = $matter->documents()
            ->with(['currentVersion', 'createdBy'])
            ->withCount('versions')
            ->latest()
            ->paginate(15);

        return DocumentResource::collection($documents);
    }

    public function show(Document $document): DocumentResource
    {
        $this->authorize('view', $document);

        return new DocumentResource(
            $document->load(['currentVersion', 'createdBy'])->loadCount('versions')
        );
    }

    public function import(ImportDocumentRequest $request, Matter $matter, DocumentImportService $importService): JsonResponse
    {
        $this->authorize('view', $matter);
        $this->authorize('create', Document::class);

        $options = array_filter([
            'title' => $request->validated('title'),
            'document_type' => $request->validated('document_type'),
            'language_primary' => $request->validated('language_primary'),
        ]);

        $document = $importService->importDocx(
            $request->file('file'),
            $matter,
            $request->user(),
            $options,
        );

        return (new DocumentResource($document->load(['currentVersion', 'createdBy'])->loadCount('versions')))
            ->response()
            ->setStatusCode(201);
    }

    public function save(SaveDocumentRequest $request, Document $document, DocumentService $documentService): JsonResponse
    {
        // Optimistic locking: reject if current_version_id doesn't match
        if ($document->current_version_id !== $request->validated('current_version_id')) {
            return response()->json([
                'error' => 'conflict',
                'message' => __('documents.save_conflict'),
                'server_version_id' => $document->current_version_id,
            ], 409);
        }

        $version = $documentService->createVersion(
            $document,
            $request->validated('body'),
            $request->user(),
            $request->validated('change_summary'),
        );

        if ($version === null) {
            return response()->json(['message' => __('documents.version_skipped')], 200);
        }

        return (new DocumentVersionResource($version))
            ->response()
            ->setStatusCode(201);
    }

    public function versions(Document $document): AnonymousResourceCollection
    {
        $this->authorize('view', $document);

        $versions = $document->versions()
            ->with('createdBy')
            ->orderByDesc('version_number')
            ->paginate(20);

        return DocumentVersionResource::collection($versions);
    }

    public function showVersion(Document $document, DocumentVersion $version): DocumentVersionResource
    {
        $this->authorize('view', $document);

        if ($version->document_id !== $document->id) {
            abort(404);
        }

        return new DocumentVersionResource($version->load('createdBy'));
    }

    public function diffVersions(Document $document, DocumentVersion $version, Request $request, VersionDiffService $diffService): JsonResponse
    {
        $this->authorize('view', $document);

        if ($version->document_id !== $document->id) {
            abort(404);
        }

        $againstId = $request->query('against');
        if (! $againstId) {
            return response()->json(['error' => 'Missing "against" query parameter'], 422);
        }

        $againstVersion = DocumentVersion::where('document_id', $document->id)
            ->where('id', $againstId)
            ->firstOrFail();

        $diff = $diffService->diff($version, $againstVersion);

        return response()->json(['data' => $diff]);
    }

    public function restoreVersion(Document $document, DocumentVersion $version, DocumentService $documentService): JsonResponse
    {
        $this->authorize('update', $document);

        if ($version->document_id !== $document->id) {
            abort(404);
        }

        $restoredVersion = $documentService->createVersion(
            $document,
            $version->body,
            auth()->user(),
            __('documents.restored_from_version', ['version' => $version->version_number]),
        );

        if ($restoredVersion === null) {
            return response()->json(['message' => __('documents.version_skipped')], 200);
        }

        return (new DocumentVersionResource($restoredVersion))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return response()->json(null, 204);
    }
}
