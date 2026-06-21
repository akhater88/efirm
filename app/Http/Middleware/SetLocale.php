<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolution order:
     * 1. ?lang=ar|en query parameter (per-request override)
     * 2. Session locale (set by locale switcher)
     * 3. Authenticated user's preferred_locale
     * 4. Workspace default_locale
     * 5. App default: 'ar'
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Query parameter override
        $queryLocale = $request->query('lang');
        if ($queryLocale && in_array($queryLocale, ['ar', 'en'], true)) {
            session(['locale' => $queryLocale]);

            return $queryLocale;
        }

        // 2. Session locale
        $sessionLocale = session('locale');
        if ($sessionLocale && in_array($sessionLocale, ['ar', 'en'], true)) {
            return $sessionLocale;
        }

        // 3. Authenticated user's preferred locale
        $user = $request->user();
        if ($user && in_array($user->preferred_locale, ['ar', 'en'], true)) {
            return $user->preferred_locale;
        }

        // 4. Workspace default locale
        if ($user) {
            $workspace = $user->currentWorkspace();
            if ($workspace && in_array($workspace->default_locale, ['ar', 'en'], true)) {
                return $workspace->default_locale;
            }
        }

        // 5. App default
        return config('app.locale', 'ar');
    }
}
