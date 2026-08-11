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

    /*
    |--------------------------------------------------------------------------
    | Attendance Statistics
    |--------------------------------------------------------------------------
    |
    | How attendance is turned into a percentage. This is an ACADEMY POLICY
    | decision, not an implementation detail (SPEC.md §8.7, §14), so it lives
    | here rather than in any controller, service body or Blade file.
    |
    | `App\Services\AttendanceStatisticsService` is the only reader of these
    | keys and the only place a percentage is calculated, so every role sees
    | the same figure for the same student over the same period.
    |
    | attended_statuses
    |   Statuses counted in the NUMERATOR — the "attended" tally.
    |   Confirmed by the academy: `late` counts as attended.
    |
    | excluded_statuses
    |   Statuses removed from the DENOMINATOR entirely, as though the session
    |   never applied to that student. `excused` is excluded, so an authorised
    |   absence neither helps nor harms the percentage.
    |   NOTE: this default has not yet been confirmed by the academy.
    |
    | A student whose every record is excluded has no percentage at all: the
    | service returns null and the UI shows the translated "not available".
    |
    */

    'attendance' => [
        'attended_statuses' => ['present', 'late'],
        'excluded_statuses' => ['excused'],
    ],
];
