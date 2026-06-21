<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InvitationController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\ProfileController;
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

    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('logout', [GoogleOAuthController::class, 'logout'])->name('logout');
});

// Invitation acceptance — public (redirects to OAuth if not auth'd)
Route::get('invitations/{token}', [InvitationController::class, 'accept'])
    ->name('invitations.accept');

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));
