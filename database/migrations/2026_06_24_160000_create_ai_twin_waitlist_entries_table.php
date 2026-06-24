<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_twin_waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('ar');
            $table->foreignUlid('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_twin_waitlist_entries');
    }
};
