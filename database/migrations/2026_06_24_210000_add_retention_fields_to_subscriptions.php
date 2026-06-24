<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('data_retention_expires_at')->nullable()->after('cancelled_at');
            $table->boolean('data_purged')->default(false)->after('data_retention_expires_at');
            $table->timestamp('data_purged_at')->nullable()->after('data_purged');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['data_retention_expires_at', 'data_purged', 'data_purged_at']);
        });
    }
};
