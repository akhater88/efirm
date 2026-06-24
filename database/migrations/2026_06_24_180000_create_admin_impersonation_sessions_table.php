<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_impersonation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users');
            $table->foreignUlid('impersonated_user_id')->constrained('users');
            $table->foreignUlid('workspace_id')->constrained();
            $table->string('purpose');
            $table->string('ip_address', 45);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('termination_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_impersonation_sessions');
    }
};
