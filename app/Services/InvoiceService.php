<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class InvoiceService
{
    /**
     * Generate the next invoice number for a workspace.
     */
    public function generateInvoiceNumber(string $workspaceId): string
    {
        $lastInvoice = Invoice::withoutGlobalScopes()
            ->where('workspace_id', $workspaceId)
            ->orderByDesc('created_at')
            ->first();

        if (! $lastInvoice) {
            return 'INV-0001';
        }

        // Extract the numeric part from the last invoice number
        $lastNumber = (int) preg_replace('/\D/', '', $lastInvoice->invoice_number);
        $nextNumber = $lastNumber + 1;

        return 'INV-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Compute invoice totals from its lines using bcmath.
     */
    public function computeTotals(Invoice $invoice): Invoice
    {
        $lines = $invoice->lines;

        $subtotal = '0.00';
        foreach ($lines as $line) {
            $subtotal = bcadd($subtotal, (string) $line->amount, 2);
        }

        $taxAmount = bcmul($subtotal, bcdiv((string) $invoice->tax_rate, '100', 6), 2);
        $total = bcadd($subtotal, $taxAmount, 2);

        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);

        return $invoice->fresh();
    }

    /**
     * Record a payment against an invoice and update its status.
     */
    public function recordPayment(Invoice $invoice, string $amount): Invoice
    {
        $newAmountPaid = bcadd((string) $invoice->amount_paid, $amount, 2);

        $status = $invoice->status;
        if (bccomp($newAmountPaid, (string) $invoice->total, 2) >= 0) {
            $status = InvoiceStatus::Paid;
        } else {
            $status = InvoiceStatus::PartiallyPaid;
        }

        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'status' => $status,
        ]);

        return $invoice->fresh();
    }
}
