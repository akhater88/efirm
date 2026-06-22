<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = JournalEntry::query()->with('lines.account');

        if ($request->has('is_posted')) {
            $query->where('is_posted', filter_var($request->input('is_posted'), FILTER_VALIDATE_BOOLEAN));
        }

        return JournalEntryResource::collection(
            $query->latest('entry_date')->paginate(15)
        );
    }

    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        $entry = DB::transaction(function () use ($request) {
            $workspace = $request->user()->currentWorkspace();

            // Auto-generate entry number
            $lastEntry = JournalEntry::withoutGlobalScopes()
                ->where('workspace_id', $workspace->id)
                ->orderByDesc('created_at')
                ->first();

            $nextNumber = $lastEntry
                ? ((int) preg_replace('/\D/', '', $lastEntry->entry_number)) + 1
                : 1;

            $entryNumber = 'JE-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'workspace_id' => $workspace->id,
                'entry_number' => $entryNumber,
                'entry_date' => $request->validated('entry_date'),
                'description' => $request->validated('description'),
                'reference' => $request->validated('reference'),
                'created_by_user_id' => $request->user()->id,
                'updated_by_user_id' => $request->user()->id,
            ]);

            foreach ($request->validated('lines') as $lineData) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $lineData['account_id'],
                    'debit' => number_format((float) $lineData['debit'], 2, '.', ''),
                    'credit' => number_format((float) $lineData['credit'], 2, '.', ''),
                    'description' => $lineData['description'] ?? null,
                ]);
            }

            return $entry;
        });

        return (new JournalEntryResource($entry->load('lines.account')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(JournalEntry $journalEntry): JournalEntryResource
    {
        $this->authorize('view', $journalEntry);

        return new JournalEntryResource($journalEntry->load('lines.account'));
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): JournalEntryResource
    {
        DB::transaction(function () use ($request, $journalEntry) {
            $journalEntry->update(array_merge(
                collect($request->validated())->except('lines')->toArray(),
                ['updated_by_user_id' => $request->user()->id]
            ));

            if ($request->has('lines')) {
                $journalEntry->lines()->delete();

                foreach ($request->validated('lines') as $lineData) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $lineData['account_id'],
                        'debit' => number_format((float) $lineData['debit'], 2, '.', ''),
                        'credit' => number_format((float) $lineData['credit'], 2, '.', ''),
                        'description' => $lineData['description'] ?? null,
                    ]);
                }
            }
        });

        return new JournalEntryResource($journalEntry->fresh()->load('lines.account'));
    }

    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        $this->authorize('delete', $journalEntry);

        if ($journalEntry->is_posted) {
            return response()->json(['message' => __('financial.journal_entry_already_posted')], 422);
        }

        $journalEntry->delete();

        return response()->json(null, 204);
    }

    public function post(JournalEntry $journalEntry): JsonResponse
    {
        $this->authorize('post', $journalEntry);

        try {
            $entry = $this->journalEntryService->post($journalEntry);

            return (new JournalEntryResource($entry->load('lines.account')))
                ->response()
                ->setStatusCode(200);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
