<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShareDownloadController;
use App\Livewire\Documents\DocumentEditor;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Locale switch — available to all users (auth + guest)
Route::post('locale/switch', [LocaleController::class, 'switch'])
    ->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('login', fn () => view('auth.login'))->name('login');
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
});

// Public share download — no auth required, rate-limited
Route::get('share/{token}', ShareDownloadController::class)
    ->name('share.download')
    ->middleware('throttle:60,60');

// Invitation acceptance — public (redirects to OAuth if not auth'd)
Route::get('invitations/{token}', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

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

            return redirect("/admin/workspace/{$workspace->slug}");
        }

        return redirect()->route('dashboard');
    })->name('dev.login');
}
