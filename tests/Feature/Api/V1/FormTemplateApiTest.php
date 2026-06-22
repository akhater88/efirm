<?php

use App\Models\FormTemplate;
use App\Models\Matter;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;

function createFormTestUser(string $role = 'owner'): array
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

// --- F-11.1: Form Template CRUD ---

it('creates a form template with fields', function () {
    [$user, $workspace] = createFormTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/form-templates', [
        'name_ar' => 'نموذج اختبار',
        'name_en' => 'Test Form',
        'fields' => [
            [
                'key' => 'full_name',
                'label_ar' => 'الاسم الكامل',
                'label_en' => 'Full Name',
                'field_type' => 'text',
                'is_required' => true,
            ],
            [
                'key' => 'notes',
                'label_ar' => 'ملاحظات',
                'label_en' => 'Notes',
                'field_type' => 'textarea',
                'is_required' => false,
            ],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name_en', 'Test Form');
    $response->assertJsonCount(2, 'data.fields');
});

it('lists form templates in current workspace only', function () {
    [$user, $workspace] = createFormTestUser();
    $otherWorkspace = Workspace::factory()->create();

    FormTemplate::factory()->create(['workspace_id' => $workspace->id]);
    FormTemplate::factory()->create(['workspace_id' => $otherWorkspace->id]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/form-templates');

    $response->assertOk();
    $response->assertJsonCount(1, 'data.data');
});

it('updates a form template and increments version when fields change', function () {
    [$user, $workspace] = createFormTestUser();

    $template = FormTemplate::factory()->withFields(2)->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/form-templates/{$template->id}", [
        'name_en' => 'Updated Form',
        'fields' => [
            [
                'key' => 'new_field',
                'label_ar' => 'حقل جديد',
                'label_en' => 'New Field',
                'field_type' => 'text',
                'is_required' => true,
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.version', 2);
    $response->assertJsonCount(1, 'data.fields');
});

it('deletes a form template', function () {
    [$user, $workspace] = createFormTestUser();

    $template = FormTemplate::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/form-templates/{$template->id}");

    $response->assertNoContent();
    $this->assertSoftDeleted('form_templates', ['id' => $template->id]);
});

it('validates required fields on form template creation', function () {
    [$user, $workspace] = createFormTestUser();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/form-templates', [
        'name_ar' => 'نموذج',
        // missing name_en and fields
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name_en', 'fields']);
});

// --- F-11.1: Form Submissions ---

it('creates a form submission with validation', function () {
    [$user, $workspace] = createFormTestUser();

    $template = FormTemplate::factory()->withFields(1)->create(['workspace_id' => $workspace->id]);
    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/form-submissions', [
        'form_template_id' => $template->id,
        'submittable_type' => 'matter',
        'submittable_id' => $matter->id,
        'values' => ['field_1' => 'Test value'],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.template_version_at_submission', 1);
});

it('rejects submission when required fields are missing', function () {
    [$user, $workspace] = createFormTestUser();

    $template = FormTemplate::factory()->create(['workspace_id' => $workspace->id]);
    // Add a required field
    $template->fields()->create([
        'key' => 'required_field',
        'label_ar' => 'حقل مطلوب',
        'label_en' => 'Required Field',
        'field_type' => 'text',
        'is_required' => true,
        'sort_order' => 0,
    ]);

    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/form-submissions', [
        'form_template_id' => $template->id,
        'submittable_type' => 'matter',
        'submittable_id' => $matter->id,
        'values' => ['other_field' => 'some value'],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['values.required_field']);
});

it('captures template version at submission time', function () {
    [$user, $workspace] = createFormTestUser();

    $template = FormTemplate::factory()->create([
        'workspace_id' => $workspace->id,
        'version' => 3,
    ]);
    $template->fields()->create([
        'key' => 'field_1',
        'label_ar' => 'حقل',
        'label_en' => 'Field',
        'field_type' => 'text',
        'is_required' => false,
        'sort_order' => 0,
    ]);

    $matter = Matter::factory()->create(['workspace_id' => $workspace->id]);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/form-submissions', [
        'form_template_id' => $template->id,
        'submittable_type' => 'matter',
        'submittable_id' => $matter->id,
        'values' => ['field_1' => 'value'],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.template_version_at_submission', 3);
});
