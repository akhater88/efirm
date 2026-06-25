<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    /**
     * Handle an incoming request.
     *
     * Locale detection order:
     * 1. URL prefix (/ar/*) — highest priority
     * 2. efirm_locale cookie
     * 3. Accept-Language header
     * 4. Default: en
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);

        $response = $next($request);

        // Set/refresh the locale cookie (365 days)
        $response->cookie(
            'efirm_locale',
            $locale,
            60 * 24 * 365, // 365 days
            '/',
            null,
            $request->isSecure(),
            false, // HttpOnly=false so JS can read it
            false,
            'Lax',
        );

        return $response;
    }

    private function resolveLocale(Request $request): string
    {
        // 1. URL prefix takes highest priority
        if ($request->segment(1) === 'ar') {
            return 'ar';
        }

        // 2. Cookie
        $cookieLocale = $request->cookie('efirm_locale');
        if (in_array($cookieLocale, ['en', 'ar'], true)) {
            // If cookie says 'ar' but we're on the root (non-ar) path,
            // redirect to /ar
            if ($cookieLocale === 'ar' && $request->path() === '/') {
                return 'ar';
            }

            return $cookieLocale;
        }

        // 3. Accept-Language header
        $acceptLanguage = $request->header('Accept-Language', '');
        if (preg_match('/^ar/i', $acceptLanguage)) {
            return 'ar';
        }

        // 4. Default: English
        return 'en';
    }
}
