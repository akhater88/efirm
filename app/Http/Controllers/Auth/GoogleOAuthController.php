<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
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
