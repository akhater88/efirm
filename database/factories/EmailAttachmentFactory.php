<?php

namespace Database\Factories;

use App\Models\EmailAttachment;
use App\Models\EmailIntegration;
use App\Models\Matter;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailAttachment>
 */
class EmailAttachmentFactory extends Factory
{
    protected $model = EmailAttachment::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'attached_to_type' => 'matter',
            'attached_to_id' => Matter::factory(),
            'email_integration_id' => EmailIntegration::factory(),
            'email_provider_id' => fake()->uuid(),
            'subject' => fake()->sentence(),
            'from_address' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to_addresses' => [fake()->safeEmail()],
            'cc_addresses' => null,
            'received_at' => now(),
            'body_snippet' => fake()->paragraph(),
            'has_attachments' => false,
            'attachment_files' => null,
            'is_outbound' => false,
        ];
    }
}
