<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\Court;
use App\Models\CourtReview;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use App\Models\Hearing;
use App\Models\Judge;
use App\Models\LibraryClause;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\ServiceLogEntry;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
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
            'library_clause' => LibraryClause::class,
            'court' => Court::class,
            'judge' => Judge::class,
            'hearing' => Hearing::class,
            'court_review' => CourtReview::class,
            'service_log_entry' => ServiceLogEntry::class,
            'team' => Team::class,
            'user' => User::class,
        ]);
    }
}
