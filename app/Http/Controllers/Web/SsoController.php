<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\WorkspaceSsoConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SsoController extends Controller
{
    /**
     * Initiate SSO login for a workspace.
     * Stub — actual SAML/OIDC redirect logic to be implemented with a SAML library.
     */
    public function login(string $workspaceSlug): JsonResponse
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();
        $ssoConfig = WorkspaceSsoConfig::withoutGlobalScopes()
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Stub: In production, this would redirect to the IdP SSO URL
        return response()->json([
            'message' => __('integrations.sso_login_stub'),
            'redirect_url' => $ssoConfig->idp_sso_url,
        ]);
    }

    /**
     * Assertion Consumer Service (ACS) endpoint for SAML callbacks.
     * Stub — actual SAML response parsing to be implemented.
     */
    public function acs(Request $request, string $workspaceSlug): JsonResponse
    {
        $workspace = Workspace::where('slug', $workspaceSlug)->firstOrFail();
        $ssoConfig = WorkspaceSsoConfig::withoutGlobalScopes()
            ->where('workspace_id', $workspace->id)
            ->where('is_active', true)
            ->firstOrFail();

        // Stub: In production, this would validate the SAML response,
        // extract user attributes, and log the user in
        return response()->json([
            'message' => __('integrations.sso_acs_stub'),
        ]);
    }
}
