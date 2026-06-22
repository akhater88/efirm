<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'invoice_id' => Invoice::factory(),
            'receipt_number' => 'RCT-'.fake()->unique()->numerify('####'),
            'amount' => '1000.00',
            'payment_method' => PaymentMethod::BankTransfer,
            'received_date' => now(),
        ];
    }
}
