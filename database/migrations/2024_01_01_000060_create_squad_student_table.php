<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('squad_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained('squads')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('enrolled_on');
            $table->date('left_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['squad_id', 'is_active']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_student');
    }
};
