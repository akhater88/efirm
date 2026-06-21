<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentShare;
use App\Services\DocumentExportService;
use Symfony\Component\HttpFoundation\Response;

class ShareDownloadController extends Controller
{
    public function __invoke(string $token, DocumentExportService $exportService): Response
    {
        // Use withTrashed to detect revoked shares and return 410 instead of 404
        $share = DocumentShare::withTrashed()->where('token', $token)->first();

        if (! $share) {
            abort(404, __('documents.share_not_found'));
        }

        if ($share->isRevoked()) {
            abort(410, __('documents.share_revoked'));
        }

        if ($share->isExpired()) {
            abort(410, __('documents.share_expired'));
        }

        // Record access
        $share->recordAccess();

        // Load the locked version
        $document = $share->document;
        $version = $share->version;

        // Generate .docx
        $content = $exportService->exportToDocx($document, $version);
        $filename = $exportService->getFilename($document, $version);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => strlen($content),
        ]);
    }
}
