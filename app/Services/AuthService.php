<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * Find or create a user from a Google OAuth response.
     *
     * Account linking rules:
     * - If no user with this email exists: create user + workspace + owner membership.
     * - If user exists with google_id = null: link google_id, log in.
     * - If user exists with matching google_id: log in.
     * - If user exists with DIFFERENT google_id: return null (conflict).
     */
    public function findOrCreateUserFromGoogle(SocialiteUser $googleUser): ?User
    {
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            if ($existingUser->google_id === null) {
                $existingUser->update(['google_id' => $googleUser->getId()]);

                return $existingUser;
            }

            if ($existingUser->google_id === $googleUser->getId()) {
                $existingUser->update([
                    'name' => $googleUser->getName(),
                    'avatar_url' => $googleUser->getAvatar(),
                ]);

                return $existingUser;
            }

            // google_id mismatch — reject
            return null;
        }

        return $this->createUserWithWorkspace($googleUser);
    }

    private function createUserWithWorkspace(SocialiteUser $googleUser): User
    {
        return DB::transaction(function () use ($googleUser) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar_url' => $googleUser->getAvatar(),
                'google_id' => $googleUser->getId(),
                'preferred_locale' => 'ar',
                'email_verified_at' => now(),
            ]);

            $firstName = explode(' ', $googleUser->getName())[0];
            $workspaceName = $firstName."'s Workspace";

            $workspace = Workspace::create([
                'name' => $workspaceName,
                'default_locale' => 'ar',
                'created_by_user_id' => $user->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
                'created_by_user_id' => $user->id,
            ]);

            return $user;
        });
    }
}
