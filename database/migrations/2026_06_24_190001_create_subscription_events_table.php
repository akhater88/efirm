<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained();
            $table->string('event_type');
            $table->string('from_state')->nullable();
            $table->string('to_state')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('triggered_by_admin_id')->nullable()->constrained('admin_users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_events');
    }
};
