<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Receipt::query()->with('invoice');

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->input('invoice_id'));
        }

        return ReceiptResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreReceiptRequest $request): JsonResponse
    {
        $receipt = DB::transaction(function () use ($request) {
            $workspace = $request->user()->currentWorkspace();

            // Auto-generate receipt number
            $lastReceipt = Receipt::withoutGlobalScopes()
                ->where('workspace_id', $workspace->id)
                ->orderByDesc('created_at')
                ->first();

            $nextNumber = $lastReceipt
                ? ((int) preg_replace('/\D/', '', $lastReceipt->receipt_number)) + 1
                : 1;

            $receiptNumber = 'RCT-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            $receipt = Receipt::create([
                'workspace_id' => $workspace->id,
                'invoice_id' => $request->validated('invoice_id'),
                'receipt_number' => $receiptNumber,
                'amount' => number_format((float) $request->validated('amount'), 2, '.', ''),
                'payment_method' => $request->validated('payment_method'),
                'received_date' => $request->validated('received_date'),
                'reference' => $request->validated('reference'),
                'notes' => $request->validated('notes'),
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            // Update invoice payment status
            $invoice = Invoice::find($request->validated('invoice_id'));
            if ($invoice) {
                $this->invoiceService->recordPayment(
                    $invoice,
                    number_format((float) $request->validated('amount'), 2, '.', '')
                );
            }

            return $receipt;
        });

        return (new ReceiptResource($receipt->load('invoice')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Receipt $receipt): ReceiptResource
    {
        $this->authorize('view', $receipt);

        return new ReceiptResource($receipt->load('invoice'));
    }

    public function update(UpdateReceiptRequest $request, Receipt $receipt): ReceiptResource
    {
        $receipt->update(array_merge(
            $request->validated(),
            ['updated_by_user_id' => $request->user()->id]
        ));

        return new ReceiptResource($receipt->fresh()->load('invoice'));
    }

    public function destroy(Receipt $receipt): JsonResponse
    {
        $this->authorize('delete', $receipt);

        $receipt->delete();

        return response()->json(null, 204);
    }
}
