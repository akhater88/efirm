<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Invoice::query()->with(['contact', 'lines']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->input('contact_id'));
        }

        return InvoiceResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $workspace = $request->user()->currentWorkspace();
            $invoiceNumber = $this->invoiceService->generateInvoiceNumber($workspace->id);

            $invoice = Invoice::create([
                'workspace_id' => $workspace->id,
                'invoice_number' => $invoiceNumber,
                'contact_id' => $request->validated('contact_id'),
                'matter_id' => $request->validated('matter_id'),
                'currency' => $request->validated('currency', 'USD'),
                'tax_rate' => number_format((float) ($request->validated('tax_rate') ?? 0), 2, '.', ''),
                'issue_date' => $request->validated('issue_date'),
                'due_date' => $request->validated('due_date'),
                'notes' => $request->validated('notes'),
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            foreach ($request->validated('lines') as $index => $lineData) {
                $amount = bcmul(
                    number_format((float) $lineData['quantity'], 2, '.', ''),
                    number_format((float) $lineData['unit_price'], 2, '.', ''),
                    2
                );

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'description' => $lineData['description'],
                    'quantity' => number_format((float) $lineData['quantity'], 2, '.', ''),
                    'unit_price' => number_format((float) $lineData['unit_price'], 2, '.', ''),
                    'amount' => $amount,
                    'sort_order' => $index,
                ]);
            }

            return $this->invoiceService->computeTotals($invoice);
        });

        return (new InvoiceResource($invoice->load(['contact', 'lines'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['contact', 'lines', 'receipts']));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        DB::transaction(function () use ($request, $invoice) {
            $invoice->update(array_merge(
                collect($request->validated())->except('lines')->toArray(),
                ['updated_by_user_id' => $request->user()->id]
            ));

            if ($request->has('lines')) {
                $invoice->lines()->delete();

                foreach ($request->validated('lines') as $index => $lineData) {
                    $amount = bcmul(
                        number_format((float) $lineData['quantity'], 2, '.', ''),
                        number_format((float) $lineData['unit_price'], 2, '.', ''),
                        2
                    );

                    InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'description' => $lineData['description'],
                        'quantity' => number_format((float) $lineData['quantity'], 2, '.', ''),
                        'unit_price' => number_format((float) $lineData['unit_price'], 2, '.', ''),
                        'amount' => $amount,
                        'sort_order' => $index,
                    ]);
                }

                $this->invoiceService->computeTotals($invoice);
            }
        });

        return new InvoiceResource($invoice->fresh()->load(['contact', 'lines']));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(null, 204);
    }
}
