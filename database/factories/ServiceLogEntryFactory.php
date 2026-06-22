<?php

namespace Database\Factories;

use App\Enums\ServiceMethod;
use App\Enums\ServiceStatus;
use App\Models\Contact;
use App\Models\Matter;
use App\Models\ServiceLogEntry;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceLogEntry>
 */
class ServiceLogEntryFactory extends Factory
{
    protected $model = ServiceLogEntry::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'matter_id' => Matter::factory(),
            'served_party_contact_id' => Contact::factory(),
            'service_method' => ServiceMethod::PersonalService,
            'service_date' => now(),
            'status' => ServiceStatus::Successful,
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => ServiceStatus::PendingProof,
        ]);
    }
}
