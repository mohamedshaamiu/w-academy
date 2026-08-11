<?php

return [
    'dashboard' => [
        'title' => 'Admin Dashboard',
        'active_students' => 'Active students',
        'students_without_login' => 'Students without a login',
        'students_without_agreement' => 'Students without a signed agreement',
        'active_squads' => 'Active squads',
        'sessions_today' => 'Sessions today',
        'attendance_rate_week' => 'Attendance rate this week',
        'recent_activity' => 'Recent activity',
    ],

    'student' => [
        'created' => 'Student created.',
        'updated' => 'Student updated.',
        'deleted' => 'Student deleted.',
        'status_updated' => "Student's status updated.",
        'guardian_linked' => 'Guardian linked to student.',
        'guardian_unlinked' => 'Guardian unlinked from student.',
        'validation' => [
            'at_least_one_guardian_remains' => 'A student must always have at least one linked guardian.',
        ],
        'index' => [
            'title' => 'Students',
            'search_placeholder' => 'Search by index number, name, or guardian phone',
            'filter_status' => 'Status',
            'filter_squad' => 'Squad',
            'filter_agreement' => 'Agreement',
            'filter_login' => 'Login',
            'agreement_signed' => 'Signed',
            'agreement_unsigned' => 'Not signed',
            'login_issued' => 'Issued',
            'login_not_issued' => 'Not issued',
        ],
        'create' => [
            'title' => 'Add Student',
        ],
        'edit' => [
            'title' => 'Edit Student',
        ],
        'show' => [
            'title' => 'Student Details',
            'guardians_heading' => 'Guardians',
            'enrolment_heading' => 'Enrolment',
            'agreement_heading' => 'Agreement status',
            'attendance_heading' => 'Attendance summary',
            'credentials_heading' => 'Login credentials',
        ],
        'guardians' => [
            'add_heading' => 'Link a guardian',
        ],
    ],

    'guardian' => [
        'created' => 'Guardian created.',
        'updated' => 'Guardian updated.',
        'deleted' => 'Guardian deleted.',
        'linked_existing_notice' => 'A user with this phone number already existed and has been linked.',
        'index' => ['title' => 'Guardians', 'search_placeholder' => 'Search by name or phone number'],
        'create' => ['title' => 'Add Guardian'],
        'edit' => ['title' => 'Edit Guardian'],
        'show' => ['title' => 'Guardian Details', 'children_heading' => 'Linked children', 'credentials_heading' => 'Login credentials'],
    ],

    'coach' => [
        'created' => 'Coach created.',
        'updated' => 'Coach updated.',
        'deleted' => 'Coach deleted.',
        'index' => ['title' => 'Coaches', 'search_placeholder' => 'Search by name or phone number'],
        'create' => ['title' => 'Add Coach'],
        'edit' => ['title' => 'Edit Coach'],
        'show' => ['title' => 'Coach Details', 'squads_heading' => 'Assigned squads', 'credentials_heading' => 'Login credentials'],
    ],

    'squad' => [
        'created' => 'Squad created.',
        'updated' => 'Squad updated.',
        'deleted' => 'Squad deleted.',
        'student_enrolled' => 'Student enrolled.',
        'student_withdrawn' => 'Student withdrawn from squad.',
        'index' => ['title' => 'Squads'],
        'create' => ['title' => 'Add Squad'],
        'edit' => ['title' => 'Edit Squad'],
        'show' => [
            'title' => 'Squad Details',
            'enrol_heading' => 'Enrol a student',
            'enrol_student_id_label' => 'Student ID',
            'enrol_hint' => "Enter the student's numeric ID (found on their profile page), not their index number.",
            'roster_heading' => 'Squad roster',
        ],
    ],

    'session' => [
        'created' => 'Session created.',
        'updated' => 'Session updated.',
        'cancelled' => 'Session cancelled.',
        'generated' => ':created session(s) created, :skipped skipped (already existed).',
        'coach_id_hint' => "Enter the coach's numeric ID (optional). Leave blank if not yet assigned.",
        'index' => [
            'title' => 'Sessions',
            'filter_squad' => 'Squad',
            'filter_from' => 'From',
            'filter_to' => 'To',
        ],
        'create' => ['title' => 'Add Session'],
        'edit' => ['title' => 'Edit Session'],
        'show' => [
            'title' => 'Session Details',
            'attendance_heading' => 'Attendance',
            'cancel_heading' => 'Cancel this session',
        ],
        'generate_button' => 'Generate sessions',
    ],

    'agreement' => [
        'created' => 'Agreement template created.',
        'updated' => 'Agreement template updated.',
        'deleted' => 'Agreement template deleted.',
        'published' => 'Agreement template published as current. Guardians will be prompted to re-sign.',
        'validation' => [
            'both_languages_required' => 'Both Dhivehi and English content must be complete before publishing.',
            'cannot_delete_current' => 'The current agreement template cannot be deleted.',
        ],
        'index' => ['title' => 'Agreement Templates'],
        'create' => ['title' => 'New Agreement Template'],
        'edit' => ['title' => 'Edit Agreement Template'],
        'show' => ['title' => 'Agreement Template'],
        'publish_confirm' => 'Publishing this version will require every guardian to re-sign. Continue?',
        'publish_button' => 'Publish as current',
        'current_badge' => 'Current',
        'clauses_heading' => 'Consent clauses',
        'fields' => [
            'version' => 'Version',
            'title' => 'Title',
            'body' => 'Body',
            'effective_from' => 'Effective from',
            'clause_key' => 'Clause key',
            'clause_label' => 'Clause label',
        ],
        'signatures' => [
            'title' => 'Signatures Register',
            'snapshot_view' => 'View stored snapshot',
            'table' => [
                'student' => 'Student',
                'guardian' => 'Guardian',
                'signed_at' => 'Signed at',
                'language' => 'Language',
            ],
        ],
    ],

    'credential' => [
        'issue_button' => 'Issue login',
        'reset_button' => 'Reset password',
        'modal_title' => 'New credentials generated',
        'username_label' => 'Username',
        'password_label' => 'Password',
        'copy_warning' => 'This password will not be shown again. Please copy it and hand it to the family or coach now.',
        'validation' => [
            'password_must_differ' => 'The new password must be different from the current password.',
        ],
    ],

    'framework' => [
        'pillar_updated' => 'Pillar updated.',
        'strike_level_updated' => 'Strike level updated.',
        'pillars' => [
            'title' => 'Framework Pillars',
            'fields' => [
                'code' => 'Code',
                'name' => 'Name',
                'description' => 'Description',
                'icon' => 'Icon',
                'sort_order' => 'Sort order',
            ],
        ],
        'strike_levels' => [
            'title' => 'Strike Levels',
            'fields' => [
                'label' => 'Label',
                'sort_order' => 'Sort order',
                'triggers_timeout' => 'Triggers timeout',
                'timeout_minutes_min' => 'Timeout minutes (minimum)',
                'timeout_minutes_max' => 'Timeout minutes (maximum)',
                'triggers_parent_alert' => 'Triggers parent alert',
                'triggers_meeting' => 'Triggers meeting',
                'triggers_suspension' => 'Triggers suspension',
            ],
        ],
    ],

    'audit' => [
        'title' => 'Audit Log',
        'causer' => 'By',
        'subject' => 'Subject',
        'action' => 'Action',
        'date' => 'Date',
    ],
];
