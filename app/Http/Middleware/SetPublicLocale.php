<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    /**
     * URL path is the ONLY source of truth:
     * - /ar/* → Arabic
     * - everything else → English
     *
     * No cookie-based redirects. Cookie only remembers preference.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1) === 'ar' ? 'ar' : 'en';

        app()->setLocale($locale);

        $response = $next($request);

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
