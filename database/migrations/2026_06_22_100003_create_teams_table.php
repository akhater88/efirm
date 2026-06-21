<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('lead_user_id', 26)->nullable();
            $table->string('parent_team_id', 26)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('lead_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->string('team_id', 26);
            $table->string('user_id', 26);
            $table->string('role_in_team', 50)->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['team_id', 'user_id']);
        });

        // Add optional responsible_team_id to matters
        Schema::table('matters', function (Blueprint $table) {
            $table->string('responsible_team_id', 26)->nullable()->after('lead_lawyer_id');
            $table->foreign('responsible_team_id')->references('id')->on('teams')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropForeign(['responsible_team_id']);
            $table->dropColumn('responsible_team_id');
        });
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
