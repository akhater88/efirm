<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (AdminUser::count() > 0) {
            $this->command->info('Admin users table is not empty — skipping seeder.');

            return;
        }

        if (app()->environment('production')) {
            $email = env('ADMIN_SEED_EMAIL');
            $name = env('ADMIN_SEED_NAME');
            $password = env('ADMIN_SEED_PASSWORD');

            if (! $email || ! $name || ! $password) {
                throw new RuntimeException(
                    'Production admin seeder requires ADMIN_SEED_EMAIL, ADMIN_SEED_NAME, and ADMIN_SEED_PASSWORD environment variables.'
                );
            }
        } else {
            $email = env('ADMIN_SEED_EMAIL', 'admin@local.test');
            $name = env('ADMIN_SEED_NAME', 'Local Admin');
            $password = env('ADMIN_SEED_PASSWORD', Str::random(16));
        }

        $admin = AdminUser::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => AdminRole::SuperAdmin,
            'locale' => 'ar',
            'email_verified_at' => now(),
        ]);

        if (! app()->environment('production')) {
            $this->command->info("Admin user seeded: {$admin->email}");
        }
    }
}
