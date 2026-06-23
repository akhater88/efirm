<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->string('court_level')->nullable()->after('litigation_status');
        });

        Schema::table('court_reviews', function (Blueprint $table) {
            $table->string('judgment_presence')->nullable()->after('outcome');
            $table->date('notified_date')->nullable()->after('judgment_presence');
        });
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropColumn('court_level');
        });

        Schema::table('court_reviews', function (Blueprint $table) {
            $table->dropColumn(['judgment_presence', 'notified_date']);
        });
    }
};
