<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\AiDocumentGeneration;
use App\Models\AuditLog;
use App\Models\Automation;
use App\Models\AutomationRun;
use App\Models\CalendarIntegration;
use App\Models\Contact;
use App\Models\Court;
use App\Models\CourtReview;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\DocumentTemplate;
use App\Models\DocumentVersion;
use App\Models\EmailAttachment;
use App\Models\EmailIntegration;
use App\Models\ExpertReport;
use App\Models\ExternalCalendarEvent;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\Hearing;
use App\Models\Invoice;
use App\Models\Judge;
use App\Models\LawyerProfile;
use App\Models\Lead;
use App\Models\LibraryClause;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\ServiceLogEntry;
use App\Models\Task;
use App\Models\TaskWorkflow;
use App\Models\TaskWorkflowApproval;
use App\Models\Team;
use App\Models\TrustAccount;
use App\Models\User;
use App\Models\Workspace;
use App\Observers\WorkspaceObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Workspace::observe(WorkspaceObserver::class);

        // Polymorphic morph map — short stable keys for all polymorphic relationships.
        // New entities added here when their Surge builds (S-08 litigation, S-09 financial).
        Relation::enforceMorphMap([
            'matter' => Matter::class,
            'contact' => Contact::class,
            'document' => Document::class,
            'document_version' => DocumentVersion::class,
            'document_clause' => DocumentClause::class,
            'obligation' => Obligation::class,
            'task' => Task::class,
            'task_workflow' => TaskWorkflow::class,
            'task_workflow_approval' => TaskWorkflowApproval::class,
            'library_clause' => LibraryClause::class,
            'court' => Court::class,
            'judge' => Judge::class,
            'hearing' => Hearing::class,
            'court_review' => CourtReview::class,
            'service_log_entry' => ServiceLogEntry::class,
            'team' => Team::class,
            'user' => User::class,
            'account' => Account::class,
            'trust_account' => TrustAccount::class,
            'invoice' => Invoice::class,
            'lawyer_profile' => LawyerProfile::class,
            'lead' => Lead::class,
            'opportunity' => Opportunity::class,
            'pipeline' => Pipeline::class,
            'ai_document_generation' => AiDocumentGeneration::class,
            'form_template' => FormTemplate::class,
            'form_submission' => FormSubmission::class,
            'automation' => Automation::class,
            'automation_run' => AutomationRun::class,
            'document_template' => DocumentTemplate::class,
            'email_integration' => EmailIntegration::class,
            'email_attachment' => EmailAttachment::class,
            'calendar_integration' => CalendarIntegration::class,
            'external_calendar_event' => ExternalCalendarEvent::class,
            'audit_log' => AuditLog::class,
            'expert_report' => ExpertReport::class,
        ]);
    }
}
