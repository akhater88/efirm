<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HealthController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShareDownloadController;
use App\Http\Controllers\Web\SsoController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Livewire\Documents\DocumentEditor;
use App\Models\User;
use App\Services\AdminImpersonationService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Locale switch — available to all users (auth + guest)
Route::post('locale/switch', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// GET-based locale switch (avoids CSRF issues inside Filament's Livewire panel)
Route::get('locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ar', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    if (auth()->check()) {
        auth()->user()->update(['preferred_locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.set');

Route::middleware('guest')->group(function () {
    Route::get('login', fn () => view('auth.login'))->name('login');

    // Email/password login
    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = auth()->user();
            $workspace = $user->workspaces()->first();
            if ($workspace) {
                $user->switchWorkspace($workspace);

                return redirect("/app/workspace/{$workspace->slug}");
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    })->name('login.submit');

    Route::get('auth/google/redirect', [GoogleOAuthController::class, 'redirect'])
        ->name('auth.google.redirect');
    Route::get('auth/google/callback', [GoogleOAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware(['auth', 'workspace'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Document editor (custom Livewire+Blade — outside Filament panel)
    Route::get('matters/{matter}/documents/{document}', DocumentEditor::class)
        ->name('documents.editor');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('logout', [GoogleOAuthController::class, 'logout'])->name('logout');

    // Stop impersonation — accessible during impersonation session
    Route::post('impersonation/stop', function () {
        app(AdminImpersonationService::class)->stop('explicit');

        return redirect('/admin');
    })->name('impersonation.stop')->withoutMiddleware(['workspace']);
});

// Stripe webhooks — no auth, no CSRF, signature-verified
Route::post('webhooks/stripe', StripeWebhookController::class)
    ->name('webhooks.stripe')
    ->withoutMiddleware([PreventRequestForgery::class]);

// Public share download — no auth required, rate-limited
Route::get('share/{token}', ShareDownloadController::class)
    ->name('share.download')
    ->middleware('throttle:60,60');

// Invitation acceptance — public (redirects to OAuth if not auth'd)
Route::get('invitations/{token}', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

// SSO routes — public (pre-auth)
Route::get('sso/{workspaceSlug}/login', [SsoController::class, 'login'])->name('sso.login');
Route::post('sso/{workspaceSlug}/acs', [SsoController::class, 'acs'])->name('sso.acs');

// Health check — public, no auth
Route::get('health', HealthController::class)->name('health');

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Dev-only login bypass — NEVER available in production
if (app()->environment('local')) {
    Route::get('dev/login/{userId?}', function (?string $userId = null) {
        $user = $userId
            ? User::findOrFail($userId)
            : User::first();

        if (! $user) {
            abort(404, 'No users in database. Run: php artisan migrate:fresh --seed');
        }

        auth()->login($user);

        $workspace = $user->workspaces()->first();
        if ($workspace) {
            $user->switchWorkspace($workspace);

            return redirect("/app/workspace/{$workspace->slug}");
        }

        return redirect()->route('dashboard');
    })->name('dev.login');

    Route::get('dev/style-guide', fn () => view('dev.style-guide'))->name('dev.style-guide');
}
