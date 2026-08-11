<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('framework_pillars', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name_dv', 100);
            $table->string('name_en', 100);
            $table->text('description_dv');
            $table->text('description_en');
            $table->string('icon', 30);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('framework_pillars');
    }
};
