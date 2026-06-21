<?php

use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentShareController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\LibraryClauseController;
use App\Http\Controllers\Api\V1\MatterController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Models\DocumentClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');

    Route::post('workspaces', [WorkspaceController::class, 'store'])
        ->name('api.v1.workspaces.store');
    Route::post('workspaces/switch', [WorkspaceController::class, 'switch'])
        ->name('api.v1.workspaces.switch');

    Route::get('workspaces/{workspace}/invitations', [InvitationController::class, 'index'])
        ->name('api.v1.invitations.index');
    Route::post('workspaces/{workspace}/invitations', [InvitationController::class, 'store'])
        ->name('api.v1.invitations.store');
    Route::delete('workspaces/{workspace}/invitations/{invitation}', [InvitationController::class, 'destroy'])
        ->name('api.v1.invitations.destroy');
    Route::post('invitations/accept', [InvitationController::class, 'accept'])
        ->name('api.v1.invitations.accept');

    Route::get('search', SearchController::class)->name('api.v1.search');

    Route::apiResource('contacts', ContactController::class);

    Route::apiResource('matters', MatterController::class);
    Route::post('matters/{matter}/counterparties', [MatterController::class, 'attachCounterparty'])
        ->name('api.v1.matters.counterparties.attach');
    Route::delete('matters/{matter}/counterparties/{contact}', [MatterController::class, 'detachCounterparty'])
        ->name('api.v1.matters.counterparties.detach');
    Route::post('matters/{matter}/lawyers', [MatterController::class, 'attachLawyer'])
        ->name('api.v1.matters.lawyers.attach');
    Route::delete('matters/{matter}/lawyers/{user}', [MatterController::class, 'detachLawyer'])
        ->name('api.v1.matters.lawyers.detach');

    // Documents
    Route::get('matters/{matter}/documents', [DocumentController::class, 'index'])
        ->name('api.v1.matters.documents.index');
    Route::post('matters/{matter}/documents/import', [DocumentController::class, 'import'])
        ->name('api.v1.matters.documents.import');
    Route::get('documents/{document}', [DocumentController::class, 'show'])
        ->name('api.v1.documents.show');
    Route::post('documents/{document}/save', [DocumentController::class, 'save'])
        ->name('api.v1.documents.save');
    Route::get('documents/{document}/versions', [DocumentController::class, 'versions'])
        ->name('api.v1.documents.versions.index');
    Route::get('documents/{document}/versions/{version}', [DocumentController::class, 'showVersion'])
        ->name('api.v1.documents.versions.show');
    Route::get('documents/{document}/versions/{version}/diff', [DocumentController::class, 'diffVersions'])
        ->name('api.v1.documents.versions.diff');
    Route::post('documents/{document}/versions/{version}/restore', [DocumentController::class, 'restoreVersion'])
        ->name('api.v1.documents.versions.restore');
    Route::get('documents/{document}/export', [DocumentController::class, 'export'])
        ->name('api.v1.documents.export');
    Route::get('documents/{document}/shares', [DocumentShareController::class, 'index'])
        ->name('api.v1.documents.shares.index');
    Route::post('documents/{document}/shares', [DocumentShareController::class, 'store'])
        ->name('api.v1.documents.shares.store');
    Route::delete('documents/{document}/shares/{share}', [DocumentShareController::class, 'destroy'])
        ->name('api.v1.documents.shares.destroy');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->name('api.v1.documents.destroy');
    Route::post('documents/{document}/insert-library-clause', [LibraryClauseController::class, 'insertIntoDocument'])
        ->name('api.v1.documents.insert-library-clause');

    // AI Operations
    Route::post('documents/{document}/ai/draft', [AiController::class, 'draft'])
        ->name('api.v1.documents.ai.draft');
    Route::post('documents/{document}/ai/review', [AiController::class, 'review'])
        ->name('api.v1.documents.ai.review');
    Route::post('documents/{document}/ai/suggest', [AiController::class, 'suggest'])
        ->name('api.v1.documents.ai.suggest');
    Route::post('documents/{document}/ai/translate', [AiController::class, 'translate'])
        ->name('api.v1.documents.ai.translate');
    Route::post('documents/{document}/ai/explain', [AiController::class, 'explain'])
        ->name('api.v1.documents.ai.explain');
    Route::post('ai-interactions/{aiInteraction}/accept', [AiController::class, 'accept'])
        ->name('api.v1.ai.accept');
    Route::post('ai-interactions/{aiInteraction}/reject', [AiController::class, 'reject'])
        ->name('api.v1.ai.reject');

    // Document clause risk flags
    Route::patch('document-clauses/{documentClause}/risk', function (Request $request, DocumentClause $documentClause) {
        $validated = $request->validate([
            'risk_position' => 'nullable|string|in:favourable,balanced,adverse',
        ]);

        $documentClause->update(['risk_position' => $validated['risk_position']]);

        return response()->json(['data' => ['risk_position' => $documentClause->fresh()->risk_position]]);
    })->name('api.v1.document-clauses.risk');

    // Library Clauses
    Route::apiResource('library/clauses', LibraryClauseController::class, ['as' => 'api.v1.library'])
        ->parameters(['clauses' => 'libraryClause']);
    Route::post('library/clauses/from-document-clause/{documentClause}', [LibraryClauseController::class, 'saveFromDocumentClause'])
        ->name('api.v1.library.clauses.from-document');
});
