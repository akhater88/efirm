<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_user_id')->nullable();
            $table->string('attempted_email', 255)->nullable();
            $table->string('event_type', 60);
            $table->string('target_type', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('payload');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at');

            $table->foreign('admin_user_id')
                ->references('id')
                ->on('admin_users')
                ->nullOnDelete();

            $table->index(['admin_user_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_log');
    }
};
