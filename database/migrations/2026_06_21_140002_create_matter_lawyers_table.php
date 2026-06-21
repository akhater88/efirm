<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matter_lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('matter_id', 26);
            $table->string('user_id', 26);
            $table->string('role', 50)->nullable();
            $table->timestamps();

            $table->unique(['matter_id', 'user_id'], 'matter_lawyers_unique');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matter_lawyers');
    }
};
