<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! session('current_workspace_id')) {
            $firstWorkspace = $user->workspaces()->first();

            if (! $firstWorkspace) {
                abort(403, __('workspace.no_workspace'));
            }

            $user->switchWorkspace($firstWorkspace);
        }

        return $next($request);
    }
}
