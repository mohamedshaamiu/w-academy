<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('framework_strike_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level')->unique();
            $table->string('label_dv', 100);
            $table->string('label_en', 100);
            $table->string('type_dv', 100);
            $table->string('type_en', 100);
            $table->text('action_dv');
            $table->text('action_en');
            $table->text('parent_role_dv');
            $table->text('parent_role_en');
            $table->boolean('triggers_timeout')->default(false);
            $table->unsignedTinyInteger('timeout_minutes_min')->nullable();
            $table->unsignedTinyInteger('timeout_minutes_max')->nullable();
            $table->boolean('triggers_parent_alert')->default(true);
            $table->boolean('triggers_meeting')->default(false);
            $table->boolean('triggers_suspension')->default(false);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('framework_strike_levels');
    }
};
