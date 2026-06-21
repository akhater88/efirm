<?php

use App\Models\Contact;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
use App\Services\VersionDiffService;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
    ]);
    $this->actingAs($this->user);
    $this->user->switchWorkspace($this->workspace);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);

    $this->documentService = app(DocumentService::class);
    $this->diffService = new VersionDiffService;

    $bodyV1 = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Agreement Title']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This agreement is entered into between Party A and Party B.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 1. Definitions']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'The following terms shall have the meanings set forth herein.']]],
            ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 2. Obligations']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Party A shall deliver the goods within 30 days.']]],
        ],
    ];

    $this->document = $this->documentService->createDocument($matter, 'Test Agreement', $bodyV1, $this->user);

    // V2: change the obligations section
    $bodyV2 = $bodyV1;
    $bodyV2['content'][5]['content'][0]['text'] = 'Party A shall deliver the goods within 60 days of the execution date.';

    $this->documentService->createVersion($this->document, $bodyV2, $this->user, 'Extended delivery period to 60 days');

    // V3: add a new section
    $bodyV3 = $bodyV2;
    $bodyV3['content'][] = ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Section 3. Governing Law']]];
    $bodyV3['content'][] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'This agreement shall be governed by the laws of Jordan.']]];

    $this->documentService->createVersion($this->document, $bodyV3, $this->user, 'Added governing law section');

    $this->document->refresh();
});

it('produces diff blocks with correct types', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();
    $v2 = $this->document->versions()->where('version_number', 2)->first();

    $result = $this->diffService->diff($v1, $v2);

    expect($result['old_version'])->toBe(1)
        ->and($result['new_version'])->toBe(2)
        ->and($result['blocks'])->toBeArray()
        ->and($result['blocks'])->not->toBeEmpty();

    $types = collect($result['blocks'])->pluck('type')->unique()->values()->all();
    foreach ($types as $type) {
        expect($type)->toBeIn(['unchanged', 'added', 'removed']);
    }
});

it('identifies changed text between V1 and V2', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();
    $v2 = $this->document->versions()->where('version_number', 2)->first();

    $result = $this->diffService->diff($v1, $v2);

    // V1 has "within 30 days." → V2 has "within 60 days of the execution date."
    $removedText = collect($result['blocks'])
        ->where('type', 'removed')
        ->pluck('text')
        ->implode('');
    $addedText = collect($result['blocks'])
        ->where('type', 'added')
        ->pluck('text')
        ->implode('');

    expect($removedText)->toContain('30')
        ->and($addedText)->toContain('60');
});

it('identifies added content between V2 and V3', function () {
    $v2 = $this->document->versions()->where('version_number', 2)->first();
    $v3 = $this->document->versions()->where('version_number', 3)->first();

    $result = $this->diffService->diff($v2, $v3);

    $addedText = collect($result['blocks'])
        ->where('type', 'added')
        ->pluck('text')
        ->implode('');

    expect($addedText)->toContain('Governing Law')
        ->and($addedText)->toContain('Jordan');
});

it('returns stats with word counts', function () {
    $v1 = $this->document->versions()->where('version_number', 1)->first();
    $v2 = $this->document->versions()->where('version_number', 2)->first();

    $result = $this->diffService->diff($v1, $v2);

    expect($result['stats'])->toHaveKeys(['added_words', 'removed_words', 'unchanged_words'])
        ->and($result['stats']['unchanged_words'])->toBeGreaterThan(0);
});

it('produces empty diff for identical versions', function () {
    $v1 = $this->document->versions()->orderBy('version_number')->first();

    $result = $this->diffService->diff($v1, $v1);

    $hasChanges = collect($result['blocks'])->whereIn('type', ['added', 'removed'])->isNotEmpty();
    expect($hasChanges)->toBeFalse();
});

it('extracts plain text from TipTap JSON', function () {
    $body = [
        'type' => 'doc',
        'content' => [
            ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
            ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello world.']]],
        ],
    ];

    $text = $this->diffService->extractPlainText($body);

    expect($text)->toContain('Title')
        ->and($text)->toContain('Hello world.');
});
