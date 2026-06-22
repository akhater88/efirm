<?php

use App\Enums\InvoiceStatus;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createReceiptUser(string $role = 'owner'): array
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

it('creates a receipt and updates invoice payment status', function () {
    [$user, $workspace] = createReceiptUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'total' => '5000.00',
        'amount_paid' => '0.00',
        'status' => InvoiceStatus::Sent,
    ]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/receipts', [
        'invoice_id' => $invoice->id,
        'amount' => 5000.00,
        'payment_method' => 'bank_transfer',
        'received_date' => '2026-06-22',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.amount', '5000.00');
    $response->assertJsonPath('data.payment_method', 'bank_transfer');
    expect($response->json('data.receipt_number'))->toStartWith('RCT-');

    // Invoice should be fully paid
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->fresh()->amount_paid)->toBe('5000.00');
});

it('marks invoice as partially paid on partial payment', function () {
    [$user, $workspace] = createReceiptUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create([
        'workspace_id' => $workspace->id,
        'contact_id' => $contact->id,
        'total' => '5000.00',
        'amount_paid' => '0.00',
        'status' => InvoiceStatus::Sent,
    ]);

    $this->actingAs($user, 'sanctum')->postJson('/api/v1/receipts', [
        'invoice_id' => $invoice->id,
        'amount' => 2000.00,
        'payment_method' => 'cash',
        'received_date' => '2026-06-22',
    ]);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::PartiallyPaid);
    expect($invoice->fresh()->amount_paid)->toBe('2000.00');
});

it('lists receipts in current workspace only', function () {
    [$user, $workspace] = createReceiptUser();
    $otherWorkspace = Workspace::factory()->create();

    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $otherContact = Contact::factory()->client()->create(['workspace_id' => $otherWorkspace->id]);
    $invoice = Invoice::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);
    $otherInvoice = Invoice::factory()->create(['workspace_id' => $otherWorkspace->id, 'contact_id' => $otherContact->id]);

    Receipt::factory()->create(['workspace_id' => $workspace->id, 'invoice_id' => $invoice->id]);
    Receipt::factory()->create(['workspace_id' => $otherWorkspace->id, 'invoice_id' => $otherInvoice->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/receipts');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

it('soft-deletes a receipt', function () {
    [$user, $workspace] = createReceiptUser();
    $contact = Contact::factory()->client()->create(['workspace_id' => $workspace->id]);
    $invoice = Invoice::factory()->create(['workspace_id' => $workspace->id, 'contact_id' => $contact->id]);
    $receipt = Receipt::factory()->create(['workspace_id' => $workspace->id, 'invoice_id' => $invoice->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/receipts/{$receipt->id}");

    $response->assertNoContent();
    expect(Receipt::find($receipt->id))->toBeNull();
    expect(Receipt::withTrashed()->find($receipt->id))->not->toBeNull();
});
