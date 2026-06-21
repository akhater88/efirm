<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use App\Models\LibraryClause;
use App\Models\Matter;
use App\Models\Obligation;
use App\Models\Task;
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
        ]);
    }
}
