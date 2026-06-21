<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->workspace = Workspace::factory()->create();
    $this->user = User::factory()->create();
    WorkspaceMember::factory()->create([
        'user_id' => $this->user->id,
        'workspace_id' => $this->workspace->id,
        'role' => Role::Owner,
    ]);
    $this->user->switchWorkspace($this->workspace);
    Sanctum::actingAs($this->user);

    $client = Contact::factory()->client()->create(['workspace_id' => $this->workspace->id]);
    $this->matter = Matter::factory()->create([
        'workspace_id' => $this->workspace->id,
        'client_id' => $client->id,
    ]);
});

it('lists documents for a matter', function () {
    Document::factory()->count(3)->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
    ]);

    $response = $this->getJson("/api/v1/matters/{$this->matter->id}/documents");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('imports a .docx file via API', function () {
    $file = new UploadedFile(
        base_path('tests/fixtures/docx/03-supply-agreement-en.docx'),
        '03-supply-agreement-en.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true,
    );

    $response = $this->postJson("/api/v1/matters/{$this->matter->id}/documents/import", [
        'file' => $file,
        'title' => 'Supply Agreement Test',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Supply Agreement Test')
        ->assertJsonPath('data.matter_id', $this->matter->id);

    expect(Document::count())->toBe(1);
});

it('rejects non-docx file with 422', function () {
    $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

    $response = $this->postJson("/api/v1/matters/{$this->matter->id}/documents/import", [
        'file' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('rejects missing file with 422', function () {
    $response = $this->postJson("/api/v1/matters/{$this->matter->id}/documents/import", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('shows a single document', function () {
    $document = Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
    ]);

    $response = $this->getJson("/api/v1/documents/{$document->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $document->id);
});

it('deletes a document (soft delete)', function () {
    $document = Document::factory()->create([
        'workspace_id' => $this->workspace->id,
        'matter_id' => $this->matter->id,
    ]);

    $response = $this->deleteJson("/api/v1/documents/{$document->id}");

    $response->assertNoContent();
    expect(Document::find($document->id))->toBeNull();
    expect(Document::withTrashed()->find($document->id))->not->toBeNull();
});
