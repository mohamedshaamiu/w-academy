<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('version')->unique();
            $table->string('title_dv', 255);
            $table->string('title_en', 255);
            $table->longText('body_dv');
            $table->longText('body_en');
            $table->json('consent_clauses');
            $table->date('effective_from');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_templates');
    }
};
