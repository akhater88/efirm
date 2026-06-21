<?php

use App\Enums\Role;
use App\Models\Contact;
use App\Models\ContractMetadata;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Services\DocumentService;
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
    $matter = Matter::factory()->create(['workspace_id' => $this->workspace->id, 'client_id' => $client->id]);

    $body = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test.']]]]];
    $this->document = app(DocumentService::class)->createDocument($matter, 'Contract', $body, $this->user);
});

it('creates contract metadata via upsert', function () {
    $response = $this->putJson("/api/v1/documents/{$this->document->id}/contract", [
        'contract_value' => 500000,
        'contract_currency' => 'USD',
        'effective_date' => '2026-06-01',
        'term_months' => 24,
        'governing_law' => 'Jordan',
    ]);

    $response->assertOk();

    $metadata = ContractMetadata::first();
    expect($metadata->contract_value)->toBe('500000.00')
        ->and($metadata->contract_currency)->toBe('USD')
        ->and($metadata->governing_law)->toBe('Jordan')
        ->and($metadata->term_months)->toBe(24);
});

it('auto-computes expiry_date from effective_date + term_months', function () {
    $this->putJson("/api/v1/documents/{$this->document->id}/contract", [
        'effective_date' => '2026-06-01',
        'term_months' => 12,
    ]);

    $metadata = ContractMetadata::first();
    expect($metadata->expiry_date->format('Y-m-d'))->toBe('2027-06-01');
});

it('updates existing contract metadata', function () {
    $this->putJson("/api/v1/documents/{$this->document->id}/contract", [
        'contract_value' => 100000,
        'governing_law' => 'Jordan',
    ]);

    $this->putJson("/api/v1/documents/{$this->document->id}/contract", [
        'contract_value' => 200000,
        'governing_law' => 'Lebanon',
    ]);

    expect(ContractMetadata::count())->toBe(1);
    expect(ContractMetadata::first()->governing_law)->toBe('Lebanon');
});

it('reads contract metadata', function () {
    ContractMetadata::create([
        'workspace_id' => $this->workspace->id,
        'document_id' => $this->document->id,
        'contract_value' => 250000,
        'contract_currency' => 'JOD',
        'governing_law' => 'Jordan',
    ]);

    $response = $this->getJson("/api/v1/documents/{$this->document->id}/contract");

    $response->assertOk()
        ->assertJsonPath('data.contract_currency', 'JOD')
        ->assertJsonPath('data.governing_law', 'Jordan');
});

it('returns null for document without metadata', function () {
    $response = $this->getJson("/api/v1/documents/{$this->document->id}/contract");

    $response->assertOk()
        ->assertJsonPath('data', null);
});
