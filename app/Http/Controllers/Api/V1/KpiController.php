<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KpiTarget;
use App\Models\Team;
use App\Services\KpiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KpiController extends Controller
{
    public function __construct(
        private KpiService $kpiService,
    ) {}

    public function myProgress(Request $request): JsonResponse
    {
        $user = $request->user();
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $targets = KpiTarget::where('targetable_type', 'user')
            ->where('targetable_id', $user->id)
            ->where('effective_from', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $start);
            })
            ->get();

        $progress = $targets->map(fn (KpiTarget $t) => $this->kpiService->getProgress($t, $start, $end));

        return response()->json(['data' => $progress]);
    }

    public function teamProgress(Request $request, Team $team): JsonResponse
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $targets = KpiTarget::where('targetable_type', 'team')
            ->where('targetable_id', $team->id)
            ->where('effective_from', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $start);
            })
            ->get();

        $progress = $targets->map(fn (KpiTarget $t) => $this->kpiService->getProgress($t, $start, $end));

        return response()->json(['data' => $progress]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'targetable_type' => 'required|string|in:user,team',
            'targetable_id' => 'required|string|size:26',
            'metric' => 'required|string|in:billable_hours_monthly,matters_opened_monthly,matters_closed_monthly,revenue_monthly',
            'target_value' => 'required|numeric|min:0',
            'period' => 'required|string|in:monthly,quarterly,annual',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        $target = KpiTarget::create(array_merge($validated, [
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]));

        return response()->json(['data' => $target], 201);
    }
}
