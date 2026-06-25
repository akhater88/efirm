<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\SeoController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HealthController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShareDownloadController;
use App\Http\Controllers\Web\SsoController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Livewire\Documents\DocumentEditor;
use App\Livewire\Pages\ContactsList;
use App\Livewire\Pages\DocumentsList;
use App\Livewire\Pages\LibraryClausesList;
use App\Livewire\Pages\MatterDetail;
use App\Livewire\Pages\MattersList;
use App\Livewire\Pages\ObligationsList;
use App\Livewire\Pages\Settings\TaskTypesSettings;
use App\Livewire\Pages\TasksList;
use App\Livewire\Pages\TimeEntriesList;
use App\Models\User;
use App\Services\AdminImpersonationService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Marketing Routes
|--------------------------------------------------------------------------
| These routes serve the public landing page and supporting pages.
| They MUST be defined before any auth routes so they take precedence.
*/

Route::middleware('public.locale')->group(function () {
    Route::get('/', function (Request $request) {
        // Authenticated users go to dashboard; guests see landing page
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // If cookie says 'ar', redirect to /ar
        $cookieLocale = $request->cookie('efirm_locale');
        if ($cookieLocale === 'ar') {
            return redirect('/ar');
        }

        // If Accept-Language prefers Arabic, redirect
        $acceptLanguage = $request->header('Accept-Language', '');
        if (! $cookieLocale && preg_match('/^ar/i', $acceptLanguage)) {
            return redirect('/ar');
        }

        return app(LandingController::class)->index($request);
    })->name('landing');

    Route::get('/demo-request', [LandingController::class, 'demoRequest'])->name('demo-request');
    Route::get('/demo-request/thank-you', [LandingController::class, 'thankYou'])->name('demo-request.thanks');
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
});

// Arabic prefix routes
Route::prefix('ar')->middleware('public.locale')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing.ar');
    Route::get('/demo-request', [LandingController::class, 'demoRequest'])->name('demo-request.ar');
    Route::get('/demo-request/thank-you', [LandingController::class, 'thankYou'])->name('demo-request.thanks.ar');
    Route::get('/{slug}', [LegalController::class, 'show'])
        ->where('slug', 'terms|privacy|dpa|ai-disclaimer')
        ->name('legal.show.ar');
});

// Legal pages (English)
Route::middleware('public.locale')->group(function () {
    Route::get('/{slug}', [LegalController::class, 'show'])
        ->where('slug', 'terms|privacy|dpa|ai-disclaimer')
        ->name('legal.show');
});

// Locale switch — available to all users (auth + guest)
Route::post('locale/switch', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// GET-based locale switch (avoids CSRF issues inside Filament's Livewire panel)
Route::get('locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ar', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    if (auth()->check()) {
        auth()->user()->update(['preferred_locale' => $locale]);
    }

    $referer = request()->headers->get('referer');

    return redirect($referer ?: '/dashboard');
})->name('locale.set');

Route::middleware('guest')->group(function () {
    Route::get('login', fn () => view('auth.login'))->name('login');
    Route::get('register', fn () => redirect()->route('login'))->name('register');

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

    // Custom Livewire list pages
    Route::get('matters', MattersList::class)->name('matters.index');
    Route::get('matters/{id}', MatterDetail::class)->name('matters.show');
    Route::get('contacts', ContactsList::class)->name('contacts.index');
    Route::get('tasks', TasksList::class)->name('tasks.index');
    Route::get('documents', DocumentsList::class)->name('documents.index');
    Route::get('obligations', ObligationsList::class)->name('obligations.index');
    Route::get('library-clauses', LibraryClausesList::class)->name('library-clauses.index');
    Route::get('time-entries', TimeEntriesList::class)->name('time-entries.index');

    // Settings pages
    Route::get('settings/task-types', TaskTypesSettings::class)->name('settings.task-types');

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

// Root route handled by public marketing routes above (with auth check)

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
        }

        return redirect()->route('dashboard');
    })->name('dev.login');

    Route::get('dev/style-guide', fn () => view('dev.style-guide'))->name('dev.style-guide');
}
