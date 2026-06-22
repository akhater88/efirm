<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AiGenerationTemplateController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AutomationController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContractMetadataController;
use App\Http\Controllers\Api\V1\CourtController;
use App\Http\Controllers\Api\V1\CourtReviewController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentShareController;
use App\Http\Controllers\Api\V1\DocumentTemplateController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\FormSubmissionController;
use App\Http\Controllers\Api\V1\FormTemplateController;
use App\Http\Controllers\Api\V1\HearingController;
use App\Http\Controllers\Api\V1\InvitationController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\JudgeController;
use App\Http\Controllers\Api\V1\KpiController;
use App\Http\Controllers\Api\V1\KycController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\LibraryClauseController;
use App\Http\Controllers\Api\V1\MatterController;
use App\Http\Controllers\Api\V1\ObligationController;
use App\Http\Controllers\Api\V1\OpportunityController;
use App\Http\Controllers\Api\V1\PipelineController;
use App\Http\Controllers\Api\V1\ReceiptController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ServiceLogEntryController;
use App\Http\Controllers\Api\V1\SmartListController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TaskWorkflowController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use App\Http\Controllers\Api\V1\TrustAccountController;
use App\Http\Controllers\Api\V1\WorkflowBundleController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Models\DocumentClause;
use App\Models\Matter;
use App\Services\AiDocumentGenerationService;
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

    Route::get('feedback', [FeedbackController::class, 'index'])->name('api.v1.feedback.index');
    Route::post('feedback', [FeedbackController::class, 'store'])->name('api.v1.feedback.store');

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
    Route::post('matters/{matter}/ai/generate-document', function (Request $request, Matter $matter) {
        $request->user()->can('update', $matter) || abort(403);
        $validated = $request->validate([
            'template_key' => 'required|string|max:100',
            'intent_payload' => 'required|array',
        ]);
        $generation = app(AiDocumentGenerationService::class)->generate(
            $validated['template_key'], $validated['intent_payload'], $matter, $request->user()
        );

        return response()->json(['data' => $generation->load('generatedDocument')], 201);
    })->name('api.v1.matters.ai.generate-document');

    // AI Generation Templates (F-10.5)
    Route::apiResource('ai-generation-templates', AiGenerationTemplateController::class)
        ->parameters(['ai-generation-templates' => 'aiGenerationTemplate']);

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

    // Contract Metadata
    Route::get('documents/{document}/contract', [ContractMetadataController::class, 'show'])
        ->name('api.v1.documents.contract.show');
    Route::put('documents/{document}/contract', [ContractMetadataController::class, 'upsert'])
        ->name('api.v1.documents.contract.upsert');

    // Obligations
    Route::get('documents/{document}/obligations', [ObligationController::class, 'index'])
        ->name('api.v1.documents.obligations.index');
    Route::post('documents/{document}/obligations', [ObligationController::class, 'store'])
        ->name('api.v1.documents.obligations.store');
    Route::get('obligations/{obligation}', [ObligationController::class, 'show'])
        ->name('api.v1.obligations.show');
    Route::patch('obligations/{obligation}', [ObligationController::class, 'update'])
        ->name('api.v1.obligations.update');
    Route::post('obligations/{obligation}/complete', [ObligationController::class, 'complete'])
        ->name('api.v1.obligations.complete');
    Route::delete('obligations/{obligation}', [ObligationController::class, 'destroy'])
        ->name('api.v1.obligations.destroy');

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])
        ->name('tasks.complete');

    // Task Workflows
    Route::apiResource('task-workflows', TaskWorkflowController::class);
    Route::post('tasks/{task}/transitions', [TaskWorkflowController::class, 'transition'])
        ->name('tasks.transitions');
    Route::post('task-approvals/{taskWorkflowApproval}/respond', [TaskWorkflowController::class, 'respondToApproval'])
        ->name('task-approvals.respond');

    // Time Entries
    Route::apiResource('time-entries', TimeEntryController::class);
    Route::get('time-entries-summary', [TimeEntryController::class, 'summary'])
        ->name('time-entries.summary');

    // KYC
    Route::get('contacts/{contact}/kyc', [KycController::class, 'show'])
        ->name('api.v1.contacts.kyc.show');
    Route::post('contacts/{contact}/kyc/start', [KycController::class, 'start'])
        ->name('api.v1.contacts.kyc.start');
    Route::patch('kyc-items/{kycItem}', [KycController::class, 'updateItem'])
        ->name('api.v1.kyc-items.update');

    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'attachMember'])
        ->name('teams.members.attach');
    Route::delete('teams/{team}/members/{userId}', [TeamController::class, 'detachMember'])
        ->name('teams.members.detach');

    // KPI
    Route::get('kpi/my-progress', [KpiController::class, 'myProgress'])
        ->name('api.v1.kpi.my-progress');
    Route::get('kpi/team/{team}/progress', [KpiController::class, 'teamProgress'])
        ->name('api.v1.kpi.team-progress');
    Route::post('kpi/targets', [KpiController::class, 'store'])
        ->name('api.v1.kpi.targets.store');

    // Smart Lists
    Route::apiResource('smart-lists', SmartListController::class)
        ->parameters(['smart-lists' => 'smartList']);

    // Litigation — Courts & Judges [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('courts', CourtController::class);
    Route::apiResource('judges', JudgeController::class);

    // Litigation — Hearings [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('hearings', HearingController::class);

    // Litigation — Court Reviews [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('court-reviews', CourtReviewController::class)
        ->parameters(['court-reviews' => 'courtReview']);

    // Litigation — Service Log [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('service-log-entries', ServiceLogEntryController::class)
        ->parameters(['service-log-entries' => 'serviceLogEntry']);

    // Financial — Chart of Accounts
    Route::apiResource('accounts', AccountController::class);

    // Financial — Trust Accounts
    Route::apiResource('trust-accounts', TrustAccountController::class)
        ->parameters(['trust-accounts' => 'trustAccount']);
    Route::post('trust-accounts/{trustAccount}/deposit', [TrustAccountController::class, 'deposit'])
        ->name('trust-accounts.deposit');
    Route::post('trust-accounts/{trustAccount}/withdraw', [TrustAccountController::class, 'withdraw'])
        ->name('trust-accounts.withdraw');

    // Financial — Journal Entries
    Route::apiResource('journal-entries', JournalEntryController::class)
        ->parameters(['journal-entries' => 'journalEntry']);
    Route::post('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])
        ->name('journal-entries.post');

    // Financial — Invoices
    Route::apiResource('invoices', InvoiceController::class);

    // Financial — Receipts
    Route::apiResource('receipts', ReceiptController::class);

    // CRM — Pipelines
    Route::apiResource('pipelines', PipelineController::class);

    // CRM — Leads
    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])
        ->name('leads.convert');

    // CRM — Opportunities
    Route::apiResource('opportunities', OpportunityController::class);
    Route::post('opportunities/{opportunity}/convert', [OpportunityController::class, 'convert'])
        ->name('opportunities.convert');

    // Form Templates (S-11 F-11.1)
    Route::apiResource('form-templates', FormTemplateController::class)
        ->parameters(['form-templates' => 'formTemplate']);
    Route::apiResource('form-submissions', FormSubmissionController::class)
        ->parameters(['form-submissions' => 'formSubmission'])
        ->only(['index', 'store', 'show', 'destroy']);

    // Automations (S-11 F-11.2)
    Route::apiResource('automations', AutomationController::class);
    Route::post('automations/{automation}/test', [AutomationController::class, 'test'])
        ->name('automations.test');

    // Document Templates (S-11 F-11.3)
    Route::apiResource('document-templates', DocumentTemplateController::class)
        ->parameters(['document-templates' => 'documentTemplate']);
    Route::post('matters/{matter}/documents/from-template', [DocumentTemplateController::class, 'createFromTemplate'])
        ->name('api.v1.matters.documents.from-template');

    // Workflow Bundles (S-11 F-11.4)
    Route::get('workflow-bundles', [WorkflowBundleController::class, 'index'])
        ->name('workflow-bundles.index');
    Route::post('workflow-bundles/{key}/activate', [WorkflowBundleController::class, 'activate'])
        ->name('workflow-bundles.activate');
});
