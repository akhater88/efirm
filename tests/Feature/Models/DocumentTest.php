<?php

use App\Enums\DocumentLanguage;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

it('can create a document with ULID primary key', function () {
    $document = Document::factory()->create();

    expect($document->id)->toHaveLength(26);
});

it('casts document_type to DocumentType enum', function () {
    $document = Document::factory()->create(['document_type' => 'contract']);

    expect($document->document_type)->toBe(DocumentType::Contract);
});

it('casts status to DocumentStatus enum', function () {
    $document = Document::factory()->create(['status' => 'draft']);

    expect($document->status)->toBe(DocumentStatus::Draft);
});

it('casts language_primary to DocumentLanguage enum', function () {
    $document = Document::factory()->create(['language_primary' => 'bilingual']);

    expect($document->language_primary)->toBe(DocumentLanguage::Bilingual);
});

it('belongs to a matter', function () {
    $matter = Matter::factory()->create();
    $document = Document::factory()->create([
        'matter_id' => $matter->id,
        'workspace_id' => $matter->workspace_id,
    ]);

    expect($document->matter->id)->toBe($matter->id);
});

it('belongs to a workspace', function () {
    $document = Document::factory()->create();

    expect($document->workspace)->not->toBeNull();
});

it('has many versions', function () {
    $workspace = Workspace::factory()->create();
    $document = Document::factory()->create(['workspace_id' => $workspace->id]);

    DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $workspace->id,
        'version_number' => 1,
    ]);
    DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $workspace->id,
        'version_number' => 2,
    ]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect($document->versions)->toHaveCount(2);
});

it('has a current version', function () {
    $workspace = Workspace::factory()->create();
    $document = Document::factory()->create(['workspace_id' => $workspace->id]);
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $workspace->id,
    ]);

    $document->update(['current_version_id' => $version->id]);

    expect($document->fresh()->currentVersion->id)->toBe($version->id);
});

it('is soft-deletable', function () {
    $document = Document::factory()->create();
    $document->delete();

    expect($document->trashed())->toBeTrue();
    expect(Document::withTrashed()->find($document->id))->not->toBeNull();
});

it('workspace isolation prevents cross-workspace access', function () {
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();

    Document::factory()->create(['workspace_id' => $ws1->id]);
    Document::factory()->create(['workspace_id' => $ws2->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    $this->actingAs($user);
    $user->switchWorkspace($ws1);

    expect(Document::count())->toBe(1);
});

it('casts metadata to array', function () {
    $document = Document::factory()->create(['metadata' => ['sector' => 'tech', 'priority' => 'high']]);

    expect($document->metadata)->toBeArray()
        ->and($document->metadata['sector'])->toBe('tech');
});
