<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Demo Owner',
            'email' => 'owner@codejob.test',
            'preferred_locale' => 'ar',
        ]);

        $workspace = Workspace::factory()->create([
            'name' => 'Demo Workspace',
            'slug' => 'demo-workspace',
            'default_locale' => 'ar',
            'created_by_user_id' => $owner->id,
        ]);

        WorkspaceMember::factory()->owner()->create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
        ]);
    }
}
