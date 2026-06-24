<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceAbsoluteTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionStarted = $request->session()->get('admin.session_started_at');
        $maxHours = config('admin.session.absolute_hours', 12);

        if ($sessionStarted && now()->diffInHours($sessionStarted) > $maxHours) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login?reason=expired');
        }

        return $next($request);
    }
}
