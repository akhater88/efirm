<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TimeEntry::with(['user', 'matter', 'document', 'task']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('matter_id')) {
            $query->where('matter_id', $request->input('matter_id'));
        }

        if ($request->filled('is_billable')) {
            $query->where('is_billable', filter_var($request->input('is_billable'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->inPeriod($request->input('from'), $request->input('to'));
        }

        return response()->json([
            'data' => $query->latest('started_at')->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', TimeEntry::class);

        $validated = $request->validate([
            'matter_id' => 'nullable|string|size:26',
            'document_id' => 'nullable|string|size:26',
            'task_id' => 'nullable|string|size:26',
            'description' => 'required|string',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'is_billable' => 'nullable|boolean',
            'billing_rate_per_hour' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $timeEntry = TimeEntry::create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $timeEntry->load(['matter', 'document', 'task'])], 201);
    }

    public function show(TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('view', $timeEntry);

        return response()->json([
            'data' => $timeEntry->load(['user', 'matter', 'document', 'task']),
        ]);
    }

    public function update(Request $request, TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('update', $timeEntry);

        $validated = $request->validate([
            'description' => 'sometimes|string',
            'duration_minutes' => 'sometimes|integer|min:1|max:1440',
            'started_at' => 'sometimes|date',
            'ended_at' => 'sometimes|date',
            'is_billable' => 'nullable|boolean',
            'billing_rate_per_hour' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
        ]);

        $timeEntry->update(array_merge($validated, [
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $timeEntry->fresh()]);
    }

    public function destroy(TimeEntry $timeEntry): JsonResponse
    {
        $this->authorize('delete', $timeEntry);

        $timeEntry->delete();

        return response()->json(null, 204);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'group_by' => 'nullable|string|in:user,matter,week',
        ]);

        $query = TimeEntry::query()
            ->inPeriod($request->input('from'), $request->input('to'));

        $groupBy = $request->input('group_by', 'user');

        $results = match ($groupBy) {
            'user' => $query->select('user_id', DB::raw('SUM(duration_minutes) as total_minutes'), DB::raw('SUM(CASE WHEN is_billable THEN duration_minutes ELSE 0 END) as billable_minutes'), DB::raw('COUNT(*) as entry_count'))
                ->groupBy('user_id')
                ->with('user:id,name')
                ->get(),
            'matter' => $query->whereNotNull('matter_id')
                ->select('matter_id', DB::raw('SUM(duration_minutes) as total_minutes'), DB::raw('SUM(CASE WHEN is_billable THEN duration_minutes ELSE 0 END) as billable_minutes'), DB::raw('COUNT(*) as entry_count'))
                ->groupBy('matter_id')
                ->get(),
            'week' => $query->select(DB::raw('YEARWEEK(started_at) as week'), DB::raw('SUM(duration_minutes) as total_minutes'), DB::raw('SUM(CASE WHEN is_billable THEN duration_minutes ELSE 0 END) as billable_minutes'), DB::raw('COUNT(*) as entry_count'))
                ->groupBy('week')
                ->orderBy('week')
                ->get(),
        };

        return response()->json(['data' => $results]);
    }
}
