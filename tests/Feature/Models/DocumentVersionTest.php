<?php

use App\Models\Document;
use App\Models\DocumentClause;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\QueryException;

it('can create a document version with ULID primary key', function () {
    $version = DocumentVersion::factory()->create();

    expect($version->id)->toHaveLength(26);
});

it('belongs to a document', function () {
    $document = Document::factory()->create();
    $version = DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $document->workspace_id,
    ]);

    expect($version->document->id)->toBe($document->id);
});

it('has many clauses', function () {
    $workspace = Workspace::factory()->create();
    $version = DocumentVersion::factory()->create(['workspace_id' => $workspace->id]);

    DocumentClause::factory()->count(3)->create([
        'document_version_id' => $version->id,
        'workspace_id' => $workspace->id,
    ]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $workspace->id]);
    $this->actingAs($user);
    $user->switchWorkspace($workspace);

    expect($version->clauses)->toHaveCount(3);
});

it('has a unique version number per document', function () {
    $document = Document::factory()->create();

    DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $document->workspace_id,
        'version_number' => 1,
    ]);

    expect(fn () => DocumentVersion::factory()->create([
        'document_id' => $document->id,
        'workspace_id' => $document->workspace_id,
        'version_number' => 1,
    ]))->toThrow(QueryException::class);
});

it('stores body as JSON and casts to array', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world']]],
        ],
    ];

    $version = DocumentVersion::factory()->create(['body' => $body]);

    expect($version->body)->toBeArray()
        ->and($version->body['type'])->toBe('doc')
        ->and($version->body['content'][0]['type'])->toBe('paragraph');
});

it('workspace isolation prevents cross-workspace access', function () {
    $ws1 = Workspace::factory()->create();
    $ws2 = Workspace::factory()->create();

    DocumentVersion::factory()->create(['workspace_id' => $ws1->id]);
    DocumentVersion::factory()->create(['workspace_id' => $ws2->id]);

    $user = User::factory()->create();
    WorkspaceMember::factory()->create(['user_id' => $user->id, 'workspace_id' => $ws1->id]);
    $this->actingAs($user);
    $user->switchWorkspace($ws1);

    expect(DocumentVersion::count())->toBe(1);
});
