<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    public function accept(Request $request, string $token): RedirectResponse
    {
        if (! $request->user()) {
            // Store token in session, redirect to OAuth
            session(['pending_invitation_token' => $token]);

            return redirect()->route('auth.google.redirect');
        }

        try {
            $invitation = $this->invitationService->accept($token, $request->user());
            $request->user()->switchWorkspace($invitation->workspace);

            return redirect()->route('filament.admin.pages.dashboard', [
                'tenant' => $invitation->workspace,
            ])->with('success', __('invitations.accepted'));
        } catch (\RuntimeException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }
}
