<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Academy Identity
    |--------------------------------------------------------------------------
    */

    'name' => env('ACADEMY_NAME', 'W-Academy'),

    /*
    |--------------------------------------------------------------------------
    | Discipline Framework
    |--------------------------------------------------------------------------
    |
    | The strike level (1-based, from framework_strike_levels.level) at which
    | a student transitions toward suspension. Table-driven: no controller,
    | service, or view may hardcode this number.
    |
    */

    'suspension_trigger_level' => (int) env('ACADEMY_SUSPENSION_TRIGGER_LEVEL', 3),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetimes (minutes)
    |--------------------------------------------------------------------------
    |
    | Students get a shorter inactivity timeout than other roles, which use
    | the default `session.lifetime` config value.
    |
    */

    'student_session_lifetime' => (int) env('ACADEMY_STUDENT_SESSION_LIFETIME', 120),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */

    'generated_password_length' => [
        'student' => 8,
        'guardian' => 10,
        'coach' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session read-only window (days)
    |--------------------------------------------------------------------------
    |
    | Sessions older than this many days become read-only for coaches.
    | Admins may still edit them.
    |
    */

    'session_readonly_after_days' => 14,
];
