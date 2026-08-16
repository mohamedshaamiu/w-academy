<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `User::getAuthIdentifierName()` is `username`, so `Auth::id()` is a username
 * rather than a numeric key. Guardians, coaches and the admin log in with a
 * phone number, which happens to be numeric; students log in with an index
 * number such as `WA-2026-001`, which is not.
 *
 * The database session driver writes `Auth::id()` into `sessions.user_id`,
 * which the Laravel skeleton declares as `foreignId` (BIGINT UNSIGNED). On
 * MySQL in strict mode that write throws inside `StartSession::terminate()` —
 * after the response has already been sent — so a student's session was never
 * persisted and they were bounced straight back to the login screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};
