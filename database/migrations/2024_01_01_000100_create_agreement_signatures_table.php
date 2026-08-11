<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_template_id')->constrained('agreement_templates');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->timestamp('signed_at');
            $table->string('signatory_name', 150);
            $table->string('signature_image_path')->nullable();
            $table->json('consents');
            $table->enum('signed_locale', ['dv', 'en']);
            $table->longText('template_snapshot');
            $table->string('ip_address', 45);
            $table->string('user_agent', 255);
            $table->enum('status', ['signed', 'revoked'])->default('signed');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('agreement_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_signatures');
    }
};
