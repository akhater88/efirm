<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly InvitationService $invitationService,
    ) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', __('auth.google_auth_failed'));
        }

        $result = $this->authService->findOrCreateUserFromGoogle($googleUser);

        if ($result === null) {
            return redirect()->route('login')
                ->with('error', __('auth.google_id_mismatch'));
        }

        Auth::login($result, remember: true);

        // Check for pending invitation token
        $pendingToken = session()->pull('pending_invitation_token');
        if ($pendingToken) {
            try {
                $invitation = $this->invitationService->accept($pendingToken, $result);
                $result->switchWorkspace($invitation->workspace);

                return redirect()->route('filament.admin.pages.dashboard', [
                    'tenant' => $invitation->workspace,
                ])->with('success', __('invitations.accepted'));
            } catch (\RuntimeException $e) {
                // Fall through to normal login flow
            }
        }

        $workspace = $result->workspaces()->first();
        if ($workspace) {
            $result->switchWorkspace($workspace);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
