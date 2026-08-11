<?php

return [
    'status' => [
        'planned' => 'Planned',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'started' => 'Session started.',
    'completed_notice' => 'Session marked as completed.',

    'validation' => [
        'squad_missing_default_times' => 'This squad has no default training times set.',
        'end_after_start' => 'The end time must be after the start time.',
        'overlapping_session' => 'This squad already has a session scheduled that overlaps with this time.',
    ],

    'fields' => [
        'squad' => 'Squad',
        'coach' => 'Coach',
        'scheduled_start' => 'Start',
        'scheduled_end' => 'End',
        'venue' => 'Venue',
        'notes' => 'Notes',
        'cancellation_reason' => 'Cancellation reason',
    ],

    'generate' => [
        'title' => 'Generate sessions from schedule',
        'from' => 'From',
        'to' => 'To',
    ],

    'read_only_notice' => 'This session is more than 14 days old and can no longer be edited by a coach.',
];
