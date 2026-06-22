<?php

use App\Enums\InvoiceStatus;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createInvoiceUser(string $role = 'owner'): array
{
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create();
    WorkspaceMember::factory()->{$role}()->create([
        'user_id' => $user->id,
        'workspace_id' => $workspace->id,
    ]);
    $user->switchWorkspace($workspace);

    return [$user, $workspace];
}

it('creates an invoice with lines and computes totals', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/invoices', [
        'contact_id' => $contact->id,
        'issue_date' => '2026-06-22',
        'due_date' => '2026-07-22',
        'tax_rate' => 16,
        'lines' => [
            [
                'description' => 'Contract review',
                'quantity' => 1,
                'unit_price' => 2000.00,
            ],
            [
                'description' => 'Due diligence',
                'quantity' => 2,
                'unit_price' => 1500.00,
            ],
        ],
    ]);

    $response->assertCreated();
    // subtotal = 2000 + 3000 = 5000
    $response->assertJsonPath('data.subtotal', '5000.00');
    // tax = 5000 * 16/100 = 800
    $response->assertJsonPath('data.tax_amount', '800.00');
    // total = 5000 + 800 = 5800
    $response->assertJsonPath('data.total', '5800.00');
    $response->assertJsonPath('data.status', 'draft');
    expect($response->json('data.invoice_number'))->toStartWith('INV-');
});

it('auto-generates sequential invoice numbers', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    $invoiceData = [
        'contact_id' => $contact->id,
        'issue_date' => '2026-06-22',
        'due_date' => '2026-07-22',
        'lines' => [
            ['description' => 'Service', 'quantity' => 1, 'unit_price' => 100],
        ],
    ];

    $r1 = $this->actingAs($user, 'sanctum')->postJson('/api/v1/invoices', $invoiceData);
    $r2 = $this->actingAs($user, 'sanctum')->postJson('/api/v1/invoices', $invoiceData);

    $num1 = $r1->json('data.invoice_number');
    $num2 = $r2->json('data.invoice_number');

    expect($num1)->toBe('INV-0001');
    expect($num2)->toBe('INV-0002');
});

it('shows a single invoice with lines', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/invoices/{$invoice->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $invoice->id);
});

it('updates an invoice', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/invoices/{$invoice->id}", [
        'status' => 'sent',
        'notes' => 'Sent via email',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', 'sent');
    $response->assertJsonPath('data.notes', 'Sent via email');
});

it('soft-deletes an invoice', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/invoices/{$invoice->id}");

    $response->assertNoContent();
    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id))->not->toBeNull();
});

it('lists invoices in current workspace only', function () {
    [$user, $workspace] = createInvoiceUser();
    $otherWorkspace = Workspace::factory()->create();

    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $otherContact = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);

    Invoice::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);
    Invoice::factory()->create(['workspace_id' => $otherWorkspace->id, 'contact_id' => $otherContact->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/invoices');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('filters invoices by status', function () {
    [$user, $workspace] = createInvoiceUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);

    Invoice::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id, 'status' => InvoiceStatus::Draft]);
    Invoice::factory()->sent()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/invoices?status=sent');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});
