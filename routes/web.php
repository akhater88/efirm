<?php

use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Web\LocaleController;
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

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('logout', [GoogleOAuthController::class, 'logout'])
        ->name('logout');
});

// Redirect root to dashboard (auth middleware will bounce to login if needed)
Route::get('/', fn () => redirect()->route('dashboard'));
