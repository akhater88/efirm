<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->currentRole();

        if (! $role || ! in_array($role->value, ['owner', 'admin'])) {
            return response()->json(['message' => __('launch.feedback_forbidden')], 403);
        }

        $feedback = Feedback::query()
            ->with('user:id,name,email')
            ->latest()
            ->paginate(15);

        return response()->json($feedback);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['sometimes', 'string', Rule::in(['general', 'bug', 'feature', 'complaint'])],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'] ?? 'general',
            'message' => $validated['message'],
            'page_url' => $validated['page_url'] ?? null,
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $feedback,
            'message' => __('launch.feedback_sent'),
        ], 201);
    }
}
