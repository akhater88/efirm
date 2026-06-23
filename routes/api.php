<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AiController;
use App\Http\Controllers\Api\V1\AiGenerationTemplateController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AutomationController;
use App\Http\Controllers\Api\V1\CalendarIntegrationController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContractMetadataController;
use App\Http\Controllers\Api\V1\CourtController;
use App\Http\Controllers\Api\V1\CourtReviewController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentShareController;
use App\Http\Controllers\Api\V1\DocumentTemplateController;
use App\Http\Controllers\Api\V1\EmailIntegrationController;
use App\Http\Controllers\Api\V1\ExpertReportController;
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
use App\Http\Controllers\Api\V1\LawyerProfileController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\LibraryClauseController;
use App\Http\Controllers\Api\V1\MatterController;
use App\Http\Controllers\Api\V1\MatterLawyerController;
use App\Http\Controllers\Api\V1\ObligationController;
use App\Http\Controllers\Api\V1\OpportunityController;
use App\Http\Controllers\Api\V1\PipelineController;
use App\Http\Controllers\Api\V1\ReceiptController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ServiceLogEntryController;
use App\Http\Controllers\Api\V1\SmartListController;
use App\Http\Controllers\Api\V1\SsoConfigController;
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

    Route::get('matters/types', [MatterController::class, 'types'])
        ->name('api.v1.matters.types');
    Route::apiResource('matters', MatterController::class);
    Route::post('matters/{matter}/counterparties', [MatterController::class, 'attachCounterparty'])
        ->name('api.v1.matters.counterparties.attach');
    Route::delete('matters/{matter}/counterparties/{contact}', [MatterController::class, 'detachCounterparty'])
        ->name('api.v1.matters.counterparties.detach');
    Route::post('matters/{matter}/lawyers', [MatterController::class, 'attachLawyer'])
        ->name('api.v1.matters.lawyers.attach');
    Route::delete('matters/{matter}/lawyers/{user}', [MatterController::class, 'detachLawyer'])
        ->name('api.v1.matters.lawyers.detach');

    // Matter Lawyer Assignments (F-13.2)
    Route::get('matters/{matter}/lawyer-assignments', [MatterLawyerController::class, 'index'])
        ->name('api.v1.matters.lawyer-assignments.index');
    Route::post('matters/{matter}/lawyer-assignments', [MatterLawyerController::class, 'store'])
        ->name('api.v1.matters.lawyer-assignments.store');
    Route::delete('matters/{matter}/lawyer-assignments/{user}', [MatterLawyerController::class, 'destroy'])
        ->name('api.v1.matters.lawyer-assignments.destroy');
    Route::put('matters/{matter}/lead-lawyer', [MatterLawyerController::class, 'updateLead'])
        ->name('api.v1.matters.lead-lawyer.update');

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

    // Time Entries — Quick Timer (F-FIX-02.4, Decision #31) — registered before apiResource
    Route::post('time-entries/start', [TimeEntryController::class, 'start'])
        ->name('time-entries.start');
    Route::get('time-entries/active', [TimeEntryController::class, 'active'])
        ->name('time-entries.active');
    Route::post('time-entries/{timeEntry}/stop', [TimeEntryController::class, 'stop'])
        ->name('time-entries.stop');

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

    // Hearing Postponement Chain (F-FIX-02.5, Decision #30)
    Route::get('hearings/{hearing}/postponement-chain', [HearingController::class, 'postponementChain'])
        ->name('hearings.postponement-chain');

    // Hearing Session History (F-FIX-02.1, Decision #28)
    Route::put('hearings/{hearing}/session', [HearingController::class, 'recordSession'])
        ->name('hearings.session.record');
    Route::get('matters/{matter}/sessions-timeline', [HearingController::class, 'sessionsTimeline'])
        ->name('matters.sessions-timeline');
    Route::post('hearings/{hearing}/action-items', [HearingController::class, 'storeActionItem'])
        ->name('hearings.action-items.store');
    Route::put('hearing-action-items/{hearingActionItem}', [HearingController::class, 'updateActionItem'])
        ->name('hearing-action-items.update');
    Route::delete('hearing-action-items/{hearingActionItem}', [HearingController::class, 'destroyActionItem'])
        ->name('hearing-action-items.destroy');

    // Court Review Trainee Dispatch (F-FIX-02.2, Decision #29) — must be before apiResource
    Route::get('court-reviews/dispatched-to-me', [CourtReviewController::class, 'dispatchedToMe'])
        ->name('court-reviews.dispatched-to-me');

    // Litigation — Court Reviews [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('court-reviews', CourtReviewController::class)
        ->parameters(['court-reviews' => 'courtReview']);

    // Court Review Trainee Dispatch actions (F-FIX-02.2, Decision #29)
    Route::post('court-reviews/{courtReview}/dispatch', [CourtReviewController::class, 'dispatch'])
        ->name('court-reviews.dispatch');
    Route::post('court-reviews/{courtReview}/complete', [CourtReviewController::class, 'complete'])
        ->name('court-reviews.complete');

    // Litigation — Service Log [HARD-STOP-LAWYER-REQUIRED]
    Route::apiResource('service-log-entries', ServiceLogEntryController::class)
        ->parameters(['service-log-entries' => 'serviceLogEntry']);

    // Litigation — Expert Reports [HARD-STOP-LAWYER-REQUIRED] (F-FIX-01.2, Decisions #3, #19)
    Route::apiResource('expert-reports', ExpertReportController::class)
        ->parameters(['expert-reports' => 'expert_report']);

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

    // Email Integrations (S-12 F-12.1)
    Route::apiResource('email-integrations', EmailIntegrationController::class)
        ->parameters(['email-integrations' => 'emailIntegration']);
    Route::post('email-integrations/{emailIntegration}/fetch', [EmailIntegrationController::class, 'fetchEmails'])
        ->name('email-integrations.fetch');
    Route::post('email-integrations/{emailIntegration}/send', [EmailIntegrationController::class, 'sendEmail'])
        ->name('email-integrations.send');
    Route::post('email-attachments', [EmailIntegrationController::class, 'attachEmail'])
        ->name('email-attachments.store');

    // Calendar Integrations (S-12 F-12.2)
    Route::apiResource('calendar-integrations', CalendarIntegrationController::class)
        ->parameters(['calendar-integrations' => 'calendarIntegration']);
    Route::post('calendar-integrations/{calendarIntegration}/events', [CalendarIntegrationController::class, 'upsertEvents'])
        ->name('calendar-integrations.events.upsert');
    Route::get('calendar-integrations/{calendarIntegration}/events', [CalendarIntegrationController::class, 'events'])
        ->name('calendar-integrations.events.index');

    // SSO Configuration (S-12 F-12.3)
    Route::apiResource('sso-configs', SsoConfigController::class)
        ->parameters(['sso-configs' => 'ssoConfig']);

    // Lawyer Profiles (F-13.1)
    Route::apiResource('lawyer-profiles', LawyerProfileController::class)
        ->parameters(['lawyer-profiles' => 'lawyerProfile']);

    // Audit Logs (S-12 F-12.5)
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->name('audit-logs.show');
});
