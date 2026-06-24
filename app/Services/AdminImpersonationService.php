<?php

namespace App\Services;

use App\Enums\AdminActivityEventType;
use App\Models\AdminImpersonationSession;
use App\Models\AdminUser;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AdminImpersonationService
{
    /**
     * Start impersonating a workspace user.
     * Enforces single-active-impersonation-per-admin invariant.
     */
    public function start(AdminUser $admin, User $user, Workspace $workspace, string $purpose): AdminImpersonationSession
    {
        return DB::transaction(function () use ($admin, $user, $workspace, $purpose) {
            // Single-active-impersonation invariant
            $active = AdminImpersonationSession::where('admin_user_id', $admin->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            if ($active) {
                throw new ConflictHttpException(__('admin.impersonation.already_active'));
            }

            $session = AdminImpersonationSession::create([
                'admin_user_id' => $admin->id,
                'impersonated_user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'purpose' => $purpose,
                'ip_address' => request()->ip(),
                'started_at' => now(),
            ]);

            AdminActivityLogService::log(
                AdminActivityEventType::ImpersonationStarted,
                $admin,
                [
                    'impersonated_user_id' => $user->id,
                    'workspace_id' => $workspace->id,
                    'purpose' => $purpose,
                    'session_id' => $session->id,
                ],
                $user,
            );

            // Store session info for middleware
            session([
                'admin_impersonation_session_id' => $session->id,
                'admin_impersonation_admin_id' => $admin->id,
                'admin_impersonation_started_at' => now()->toIso8601String(),
            ]);

            // Switch to web guard as impersonated user
            auth()->guard('web')->login($user);
            $user->switchWorkspace($workspace);

            return $session;
        });
    }

    /**
     * Stop impersonation and return to admin context.
     */
    public function stop(string $reason = 'explicit'): void
    {
        $sessionId = session('admin_impersonation_session_id');
        $adminId = session('admin_impersonation_admin_id');

        if (! $sessionId || ! $adminId) {
            return;
        }

        $impersonationSession = AdminImpersonationSession::find($sessionId);

        if ($impersonationSession && $impersonationSession->isActive()) {
            $impersonationSession->update([
                'ended_at' => now(),
                'termination_reason' => $reason,
            ]);

            AdminActivityLogService::log(
                AdminActivityEventType::ImpersonationEnded,
                AdminUser::find($adminId),
                [
                    'session_id' => $sessionId,
                    'termination_reason' => $reason,
                    'duration_minutes' => (int) $impersonationSession->started_at->diffInMinutes(now()),
                ],
                $impersonationSession->impersonatedUser,
            );
        }

        // Clear impersonation session data
        session()->forget([
            'admin_impersonation_session_id',
            'admin_impersonation_admin_id',
            'admin_impersonation_started_at',
        ]);

        // Logout web user
        auth()->guard('web')->logout();
    }

    /**
     * Get the active impersonation session for an admin, if any.
     */
    public function getActiveSession(AdminUser $admin): ?AdminImpersonationSession
    {
        return AdminImpersonationSession::where('admin_user_id', $admin->id)
            ->whereNull('ended_at')
            ->first();
    }

    /**
     * Check if the current request is an impersonation session.
     */
    public static function isImpersonating(): bool
    {
        return session()->has('admin_impersonation_session_id');
    }
}
