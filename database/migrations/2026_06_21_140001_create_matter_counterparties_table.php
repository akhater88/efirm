<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matter_counterparties', function (Blueprint $table) {
            $table->id();
            $table->string('matter_id', 26);
            $table->string('contact_id', 26);
            $table->string('representing')->default('no_counsel');
            $table->timestamps();

            $table->unique(['matter_id', 'contact_id'], 'matter_counterparties_unique');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matter_counterparties');
    }
};
