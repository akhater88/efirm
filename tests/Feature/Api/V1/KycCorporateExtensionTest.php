<?php

/**
 * F-FIX-01.6 — Corporate KYC extension tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decision #12.
 */

use App\Enums\KycItemType;
use App\Enums\Role;
use App\Models\Contact;
use App\Models\KycChecklist;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
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
});

it('seeds 7 items for organization KYC including two new corporate items', function () {
    $org = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'organization',
        'organization_name' => 'Test Corp',
        'display_name' => 'Test Corp',
    ]);

    $response = $this->postJson("/api/v1/contacts/{$org->id}/kyc/start");
    $response->assertCreated();

    $checklist = KycChecklist::where('contact_id', $org->id)->first();
    $itemTypes = $checklist->items->pluck('item_type')->map(fn ($t) => $t->value)->toArray();

    expect($checklist->items)->toHaveCount(7)
        ->and($itemTypes)->toContain('company_registration_certificate')
        ->and($itemTypes)->toContain('signatory_authority_document');
});

it('person KYC count remains unchanged at 7', function () {
    $person = Contact::factory()->create([
        'workspace_id' => $this->workspace->id,
        'type' => 'person',
    ]);

    $response = $this->postJson("/api/v1/contacts/{$person->id}/kyc/start");
    $response->assertCreated();

    $checklist = KycChecklist::where('contact_id', $person->id)->first();
    expect($checklist->items)->toHaveCount(7);
});

it('new KYC item type labels render correctly', function () {
    app()->setLocale('en');
    expect(KycItemType::CompanyRegistrationCertificate->label())->toBe('Company Registration Certificate')
        ->and(KycItemType::SignatoryAuthorityDocument->label())->toBe('Signatory Authority Document');

    app()->setLocale('ar');
    expect(KycItemType::CompanyRegistrationCertificate->label())->toBe('شهادة تسجيل الشركة')
        ->and(KycItemType::SignatoryAuthorityDocument->label())->toBe('شهادة مفوضين بالتوقيع');
});
