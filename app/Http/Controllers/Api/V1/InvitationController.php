<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Services\InvitationService;
use App\Services\SubscriptionEntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('inviteMember', $workspace);

        $invitations = WorkspaceInvitation::where('workspace_id', $workspace->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        return response()->json(['data' => $invitations]);
    }

    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('inviteMember', $workspace);

        $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,member',
        ]);

        $entitlements = app(SubscriptionEntitlementService::class);

        if ($entitlements->getSubscription($workspace) && ! $entitlements->canAddSeat($workspace)) {
            return response()->json(['message' => __('admin.entitlements.seat_limit_reached')], 422);
        }

        try {
            $invitation = $this->invitationService->invite(
                $workspace,
                $request->input('email'),
                Role::from($request->input('role')),
                $request->user(),
            );

            return response()->json(['data' => $invitation], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Workspace $workspace, WorkspaceInvitation $invitation): JsonResponse
    {
        $this->authorize('inviteMember', $workspace);

        if ($invitation->workspace_id !== $workspace->id) {
            abort(404);
        }

        $invitation->delete();

        return response()->json(null, 204);
    }

    public function accept(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $invitation = $this->invitationService->accept(
                $request->input('token'),
                $request->user(),
            );

            return response()->json([
                'message' => __('invitations.accepted'),
                'workspace_id' => $invitation->workspace_id,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
