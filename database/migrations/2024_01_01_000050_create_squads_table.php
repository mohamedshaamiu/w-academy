<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->string('name_dv', 100);
            $table->string('name_en', 100);
            $table->string('age_group', 30);
            $table->foreignId('head_coach_id')->nullable()->constrained('coaches')->nullOnDelete();
            $table->string('venue_dv', 150)->nullable();
            $table->string('venue_en', 150)->nullable();
            $table->json('training_days');
            $table->time('default_start_time')->nullable();
            $table->time('default_end_time')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('head_coach_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squads');
    }
};
