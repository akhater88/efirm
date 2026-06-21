<?php

namespace App\Services;

use App\Enums\Role;
use App\Mail\WorkspaceInvitationMail;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function invite(Workspace $workspace, string $email, Role $role, User $invitedBy): WorkspaceInvitation
    {
        // Rate limit: max 5 invitations per email per day
        $recentCount = WorkspaceInvitation::where('email', $email)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($recentCount >= 5) {
            throw new \RuntimeException(__('invitations.rate_limit_exceeded'));
        }

        // Check if already a member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->belongsToWorkspace($workspace)) {
            throw new \RuntimeException(__('invitations.already_member'));
        }

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'email' => $email,
            'role' => $role,
            'token' => Str::random(64),
            'invited_by_user_id' => $invitedBy->id,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new WorkspaceInvitationMail($invitation));

        return $invitation;
    }

    public function accept(string $token, User $user): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            throw new \RuntimeException(__('invitations.already_accepted'));
        }

        if ($invitation->isExpired()) {
            throw new \RuntimeException(__('invitations.expired'));
        }

        if ($user->email !== $invitation->email) {
            throw new \RuntimeException(__('invitations.email_mismatch'));
        }

        // Add user to workspace
        WorkspaceMember::firstOrCreate(
            [
                'workspace_id' => $invitation->workspace_id,
                'user_id' => $user->id,
            ],
            [
                'role' => $invitation->role,
                'joined_at' => now(),
                'created_by_user_id' => $invitation->invited_by_user_id,
            ]
        );

        $invitation->update(['accepted_at' => now()]);

        return $invitation;
    }
}
