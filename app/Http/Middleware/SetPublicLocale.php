<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    /**
     * Locale detection:
     * - /ar/* path → Arabic
     * - / path → English
     * - First visit to / with cookie=ar → redirect to /ar
     * - First visit to / with Accept-Language: ar → redirect to /ar
     *
     * URL path is the source of truth once the user is on a page.
     * Cookie remembers their choice for the NEXT visit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isArPrefix = $request->segment(1) === 'ar';

        // If on /ar/*, locale is Arabic
        if ($isArPrefix) {
            app()->setLocale('ar');

            return $this->withLocaleCookie($next($request), 'ar', $request);
        }

        // If on root / — check if we should redirect to /ar
        if ($request->path() === '/' && ! $request->has('lang')) {
            $cookieLocale = $request->cookie('efirm_locale');

            if ($cookieLocale === 'ar') {
                return redirect('/ar');
            }

            // Check Accept-Language for first-time visitors (no cookie)
            if (! $cookieLocale) {
                $acceptLang = $request->header('Accept-Language', '');
                if (preg_match('/^ar/i', $acceptLang)) {
                    return redirect('/ar');
                }
            }
        }

        // Everything else: English
        app()->setLocale('en');

        return $this->withLocaleCookie($next($request), 'en', $request);
    }

    private function withLocaleCookie(Response $response, string $locale, Request $request): Response
    {
        $response->cookie(
            'efirm_locale',
            $locale,
            60 * 24 * 365,
            '/',
            null,
            $request->isSecure(),
            false,
            false,
            'Lax',
        );

        return $response;
    }
}
