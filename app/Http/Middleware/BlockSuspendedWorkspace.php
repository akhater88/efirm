<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspendedWorkspace
{
    /**
     * Handle an incoming request.
     *
     * Block write operations (POST/PUT/PATCH/DELETE) for suspended workspaces.
     * GET requests pass through for read-only access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        $workspaceId = session('current_workspace_id');

        if (! $workspaceId) {
            return $next($request);
        }

        $subscription = Subscription::where('workspace_id', $workspaceId)->first();

        if (! $subscription) {
            return $next($request);
        }

        if ($subscription->isSuspended()) {
            return response()->json([
                'message' => __('admin.entitlements.suspended_read_only'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
