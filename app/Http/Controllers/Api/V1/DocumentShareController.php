<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDocumentShareRequest;
use App\Http\Resources\DocumentShareResource;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\DocumentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class DocumentShareController extends Controller
{
    public function index(Document $document): AnonymousResourceCollection
    {
        $this->authorize('view', $document);

        $shares = $document->shares()
            ->with('createdBy')
            ->latest()
            ->paginate(20);

        return DocumentShareResource::collection($shares);
    }

    public function store(CreateDocumentShareRequest $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $versionId = $request->validated('version_id') ?? $document->current_version_id;

        // Verify version belongs to this document
        $version = DocumentVersion::where('document_id', $document->id)
            ->where('id', $versionId)
            ->firstOrFail();

        $share = DocumentShare::create([
            'workspace_id' => $document->workspace_id,
            'document_id' => $document->id,
            'version_id' => $version->id,
            'token' => Str::random(64),
            'recipient_email' => $request->validated('recipient_email'),
            'format' => $request->validated('format') ?? 'docx',
            'expires_at' => $request->validated('expires_at'),
            'created_by_user_id' => $request->user()->id,
        ]);

        return (new DocumentShareResource($share->load('createdBy')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Document $document, DocumentShare $share): JsonResponse
    {
        $this->authorize('delete', $share);

        if ($share->document_id !== $document->id) {
            abort(404);
        }

        $share->delete();

        return response()->json(null, 204);
    }
}
