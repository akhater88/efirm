<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('####'),
            'contact_id' => Contact::factory()->client(),
            'status' => InvoiceStatus::Draft,
            'currency' => 'USD',
            'subtotal' => '0.00',
            'tax_rate' => '0.00',
            'tax_amount' => '0.00',
            'total' => '0.00',
            'amount_paid' => '0.00',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => InvoiceStatus::Sent]);
    }

    public function paid(): static
    {
        return $this->state(['status' => InvoiceStatus::Paid]);
    }
}
