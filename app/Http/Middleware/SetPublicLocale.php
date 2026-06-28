<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $isArPrefix = $request->segment(1) === 'ar';

        if ($isArPrefix) {
            app()->setLocale('ar');

            return $this->withLocaleCookie($next($request), 'ar', $request);
        }

        // On non-/ar paths: always English.
        // Only redirect to /ar on very first visit (no cookie at all)
        // based on Accept-Language header.
        if ($request->path() === '/') {
            $cookieLocale = $request->cookie('efirm_locale');

            // No cookie at all — check Accept-Language for first-time visitors
            if ($cookieLocale === null) {
                $acceptLang = $request->header('Accept-Language', '');
                if (preg_match('/^ar/i', $acceptLang)) {
                    return redirect('/ar');
                }
            }
        }

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
