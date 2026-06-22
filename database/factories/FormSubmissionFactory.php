<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'form_template_id' => FormTemplate::factory(),
            'template_version_at_submission' => 1,
            'submittable_type' => 'matter',
            'submittable_id' => Matter::factory(),
            'submitted_at' => now(),
            'values' => ['field_1' => 'test value'],
        ];
    }
}
