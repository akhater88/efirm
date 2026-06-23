<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('user_id', 26)->unique();
            $table->string('bar_admission_number', 100)->nullable();
            $table->char('bar_admission_country', 2)->nullable();
            $table->date('bar_admission_date')->nullable();
            $table->json('jurisdictions')->nullable();
            $table->json('practice_areas')->nullable();
            $table->json('languages_spoken')->nullable();
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            $table->char('default_currency', 3)->nullable();
            $table->string('position_title_ar', 150)->nullable();
            $table->string('position_title_en', 150)->nullable();
            $table->text('bio_ar')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('status')->default('active');
            $table->date('joined_firm_date')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_profiles');
    }
};
