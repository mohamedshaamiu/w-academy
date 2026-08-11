<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('index_number', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name', 150);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('school_name', 150)->nullable();
            $table->string('class_level', 50)->nullable();
            $table->string('address', 255);
            $table->string('photo_path')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'inactive'])->default('pending');
            $table->timestamp('registered_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
            $table->index('status');
            $table->index('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
