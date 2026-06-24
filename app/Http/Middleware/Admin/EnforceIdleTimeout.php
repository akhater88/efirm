<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('admin.last_activity_at');
        $maxIdleMinutes = config('admin.session.idle_minutes', 60);

        if ($lastActivity && now()->diffInMinutes($lastActivity) > $maxIdleMinutes) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login?reason=idle');
        }

        $request->session()->put('admin.last_activity_at', now());

        return $next($request);
    }
}
