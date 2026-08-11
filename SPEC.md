# W-ACADEMY MANAGEMENT SYSTEM — PHASE 1 SPECIFICATION

> **Status:** Acceptance contract for Phase 1.
> **Authority:** This document is the sole authority on what Phase 1 requires.
> Where any prompt, audit, inferred spec, or prior implementation disagrees with
> this document, this document wins.
> **Scope note:** This is Phase 1 of a multi-phase build. Anything listed in §10
> is explicitly out of scope and must not be built, scaffolded, stubbed, or
> pre-wired.

---

## 1. OBJECTIVE

Build the Laravel web application for W-Academy, a youth football academy in the
Maldives. The academy runs a behaviour framework built on 4 life pillars (home,
school, religion, sport) and a graduated strike-based discipline policy. This is
a free CSR project delivered by Devcity.

Phase 1 delivers the foundation: a fully bilingual Dhivehi/English portal;
role-based authentication for Admin / Coach / Guardian / Student; ADMIN-ONLY
creation of students, guardians and coaches (there is no public or self-service
registration of any kind); mandatory digital signing of the discipline agreement
by the guardian, which gates both the guardian and the student portals; squad
enrolment; training session scheduling; attendance marking; and a read-only
Framework Reference Guide.

The behaviour tracker, strike engine, badges, chat, and mobile app are later
phases — but the schema and API surface built here must not obstruct them.

---

## 2. TECH STACK (do not deviate)

- PHP 8.2+, Laravel 11.x (latest LTS), MySQL 8
- Blade + Tailwind CSS (via Vite), Alpine.js. No React/Vue.
- spatie/laravel-permission for roles & permissions
- spatie/laravel-activitylog for audit trail
- intervention/image for photo + signature handling
- Laravel Breeze (Blade stack) as the auth starting point, then customised
- Deployment target: VPS, CyberPanel/OpenLiteSpeed, deployed via Git + SSH

No package outside this list may be added without a stated justification in
`README.md`.

---

## 3. LANGUAGE & LOCALISATION — HARD REQUIREMENT

This is a first-class requirement, not styling. Any monolingual surface is a
build failure.

### 3.1 Locales

- EXACTLY TWO locales are supported: `dv` (Dhivehi, Thaana script, RTL) and `en`
  (English, LTR). No third locale may be added, and no locale-detection logic may
  fall through to anything else.
- `dv` is the DEFAULT for guests and the default for newly created users.
- `config('app.locale')` = `dv`, `config('app.fallback_locale')` = `en`.

### 3.2 Switching & persistence

- A language toggle appears in the header of EVERY page — public, auth screens,
  and all four portals. It shows "ދިވެހި" and "English".
- Route: `GET /locale/{locale}` → `LocaleController@switch`, validating the value
  is in `['dv','en']` and rejecting anything else with a 404.
- Persistence order: authenticated user → write to `users.locale` AND session.
  Guest → session only. `SetLocale` middleware resolves in the order
  session → `users.locale` → `dv`, and runs on every web request.
- Switching language returns the user to the page they were on, preserving query
  strings.
- The API reads the `Accept-Language` header (`dv` or `en`) and falls back to the
  authenticated user's `locale` column.

### 3.3 Interface strings

- ZERO hardcoded user-facing strings in Blade, controllers, services, enums,
  validation, mail, or JS. Everything passes through `__()` or `@lang`.
- Translation files live in `lang/dv/*.php` and `lang/en/*.php`, split by domain:
  `common`, `auth`, `validation`, `nav`, `agreement`, `student`, `guardian`,
  `coach`, `squad`, `session`, `attendance`, `framework`, `admin`, `report`.
- Both directories MUST contain identical key sets. A missing key in either
  language is a build failure — enforced by test (§13).
- Enum labels are translated via a `label()` method reading a lang key, never by
  echoing the raw enum value.
- Validation messages are fully translated in BOTH languages, including custom
  rules (Maldivian phone format, index number format, age range, agreement
  clauses). `lang/dv/validation.php` must be genuinely translated, not a copy of
  the English file.

### 3.4 Direction & typography

- `<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">`
- Use Tailwind logical properties throughout (`ms-*`, `me-*`, `ps-*`, `pe-*`,
  `text-start`, `text-end`). Do NOT use `ml-*`, `mr-*`, `pl-*`, `pr-*`,
  `text-left`, `text-right` for layout that must mirror.
- A **licensed** Thaana webfont is bundled locally in `resources/fonts/`, applied
  via a `.font-thaana` class and set as the body font ONLY when locale is `dv`.
  It must never be set as the global `sans` family. Do not hotlink a CDN — the
  VPS must serve it. Do not bundle a proprietary font without a licence
  permitting web redistribution.
- Icons, chevrons, progress bars and any directional affordance must mirror under
  RTL.
- Numbers use Western Arabic numerals (0-9) in BOTH locales.
- Dates: format via a `FormatsDates` helper — `d/m/Y` in both locales, with month
  and weekday NAMES translated. Never call `->format()` with a hardcoded English
  month name in a view.

### 3.5 Content data (not just interface)

- Every user-facing CONTENT column is stored as a paired `*_dv` / `*_en` pair, as
  specified in §4. There is no single-language content column and no JSON
  translation blob.
- Admin forms that create or edit content expose BOTH language fields side by
  side, and BOTH are required — validation rejects saving one without the other.
- Rendering picks the column matching the active locale, falling back to the other
  only if the active one is empty, and when it falls back it displays a small
  translated "not available in this language" hint.
- Free-text entered by staff (student names, addresses, remarks, cancellation
  reasons, session notes) is stored as typed, in whatever script was used. It is
  NOT translated and NOT duplicated.
- Database charset `utf8mb4`, collation `utf8mb4_unicode_ci`, set explicitly in
  `config/database.php`, so Thaana stores and sorts correctly.

### 3.6 Source content

- Seed real Dhivehi text ONLY where it has been supplied. Everywhere the source
  documents were unreadable, seed the literal string `[DV CONTENT PENDING]` and
  seed the English column with `[EN CONTENT PENDING]`. Do NOT fabricate,
  machine-translate, or invent Dhivehi or English content.

---

## 4. DATABASE SCHEMA (Laravel migrations)

Use `foreignId()->constrained()`, explicit `onDelete` behaviour, and add indexes
on every foreign key and on every column used in a WHERE or ORDER BY below.

### users
- `id`, `name` (string 150)
- `username` (string 30, unique, required) — the login identifier. For students
  this is their index number; for guardians, coaches and admins it is their
  7-digit phone number. Stored normalised (trimmed, uppercase).
- `email` (string, nullable, unique)
- `phone` (string 20, nullable, unique) — required for guardian/coach/admin,
  nullable for students
- `password`, `locale` (enum dv/en, default dv)
- `must_change_password` (boolean, default true)
- `is_active` (boolean, default true), `last_login_at` (timestamp nullable)
- `remember_token`, timestamps, softDeletes
- Index on `username`, index on `is_active`
- There is NO self-registration route. Users exist only because an admin created
  them.

### guardians (profile extension of a user)
- `id`, `user_id` (FK users, cascade), `national_id` (string 20, nullable),
  `address` (string 255), `occupation` (string 100, nullable), timestamps
- Unique index on `user_id`

### coaches (profile extension of a user)
- `id`, `user_id` (FK users, cascade), `coach_no` (string 20, unique),
  `specialisation` (string 100, nullable), `joined_on` (date), timestamps
- Unique index on `user_id`

### students
- `id`
- `index_number` (string 20, unique, REQUIRED) — academy-issued, entered by the
  admin, and the student's login username. Validation: 3-20 characters, uppercase
  letters, digits and hyphens only (`/^[A-Z0-9\-]{3,20}$/`), stored uppercase and
  trimmed.
- `user_id` (FK users, nullable, nullOnDelete) — the student's own login account.
  Nullable because an admin may create a student record before issuing a login.
- `full_name` (string 150), `date_of_birth` (date), `gender` (enum male/female)
- `school_name` (string 150, nullable), `class_level` (string 50, nullable)
- `address` (string 255), `photo_path` (string, nullable)
- `status` (enum: pending, active, suspended, inactive; default pending)
- `registered_at` (timestamp nullable) — set when the admin creates the record
- `created_by` (FK users) — the admin who created it
- timestamps, softDeletes
- Unique index on `index_number`, unique index on `user_id`, index on `status`,
  index on `date_of_birth`

### guardian_student (pivot, many-to-many)
- `id`, `guardian_id` (FK), `student_id` (FK), `relationship` (string 50, e.g.
  father, mother, guardian), `is_primary` (boolean default false),
  `receives_alerts` (boolean default true), timestamps
- Unique composite index (`guardian_id`, `student_id`)
- Every student must have at least one guardian with `is_primary` = true —
  enforced in the service layer.

### squads
- `id`, `name_dv` (string 100), `name_en` (string 100), `age_group` (string 30,
  e.g. U-12), `head_coach_id` (FK coaches, nullable, nullOnDelete)
- `venue_dv` (string 150, nullable), `venue_en` (string 150, nullable)
- `training_days` (json — array of ISO weekday ints)
- `default_start_time` (time, nullable), `default_end_time` (time, nullable)
- `capacity` (unsigned smallint, nullable)
- `is_active` (boolean default true), timestamps

### squad_student (enrolment)
- `id`, `squad_id` (FK), `student_id` (FK), `enrolled_on` (date), `left_on` (date
  nullable), `is_active` (boolean default true), timestamps
- Index (`squad_id`, `is_active`). A student may hold only ONE active enrolment at
  a time — enforced in the service layer, not with a DB constraint.

### training_sessions
- `id`, `squad_id` (FK), `coach_id` (FK coaches, nullable)
- `scheduled_start` (datetime), `scheduled_end` (datetime)
- `venue_dv` (string 150, nullable), `venue_en` (string 150, nullable)
- `status` (enum: planned, in_progress, completed, cancelled; default planned)
- `cancellation_reason` (text nullable), `notes` (text nullable)
- `created_by` (FK users), timestamps
- Index (`squad_id`, `scheduled_start`), index on `status`

### attendances
- `id`, `training_session_id` (FK), `student_id` (FK)
- `status` (enum: present, absent, late, excused)
- `remark` (string 255 nullable)
- `marked_by` (FK users), `marked_at` (timestamp)
- timestamps
- Unique composite (`training_session_id`, `student_id`)

### agreement_templates
- `id`, `version` (unsigned smallint, unique)
- `title_dv` (string 255), `title_en` (string 255)
- `body_dv` (longtext), `body_en` (longtext)
- `consent_clauses` (json — array of `{key, label_dv, label_en, required:boolean}`)
- `effective_from` (date), `is_current` (boolean default false), timestamps
- Exactly one row may have `is_current` = true; enforced in the service layer.
- Publishing a version requires BOTH language bodies and BOTH labels on every
  clause to be non-empty. Reject publication otherwise.

### agreement_signatures
- `id`, `agreement_template_id` (FK), `student_id` (FK), `guardian_id` (FK)
- `signed_at` (timestamp), `signatory_name` (string 150) — typed full name
- `signature_image_path` (string, nullable) — drawn signature PNG
- `consents` (json — `{clause_key: true}`) — snapshot of what was agreed
- `signed_locale` (enum dv/en) — the language the guardian actually read
- `template_snapshot` (longtext) — the full body text IN `signed_locale`, stored
  at signing time so a later template edit cannot alter what was legally agreed
- `ip_address` (string 45), `user_agent` (string 255)
- `status` (enum: signed, revoked; default signed)
- `revoked_at` (timestamp nullable), `revoked_reason` (text nullable)
- timestamps
- Index (`student_id`, `status`)

### framework_pillars (reference content, seeded)
- `id`, `code` (string 20, unique), `name_dv` (string 100), `name_en` (string 100)
- `description_dv` (text), `description_en` (text), `icon` (string 30),
  `sort_order` (unsigned tinyint), `is_active` (boolean default true), timestamps

### framework_strike_levels (reference content, seeded — TABLE-DRIVEN)
- `id`, `level` (unsigned tinyint, unique), `label_dv`, `label_en` (string 100)
- `type_dv`, `type_en` (string 100) — the "type" column of the source table
- `action_dv`, `action_en` (text) — action taken by the coach
- `parent_role_dv`, `parent_role_en` (text) — the parent's role at this level
- `triggers_timeout` (boolean default false)
- `timeout_minutes_min` (unsigned tinyint nullable)
- `timeout_minutes_max` (unsigned tinyint nullable)
- `triggers_parent_alert` (boolean default true)
- `triggers_meeting` (boolean default false)
- `triggers_suspension` (boolean default false)
- `sort_order` (unsigned tinyint), `is_active` (boolean default true), timestamps

### activity_log
Provided by spatie/laravel-activitylog. Log all writes to `students`, `users`,
`guardian_student`, `agreement_signatures`, `squad_student` and `attendances`.

---

## 5. MODELS + RELATIONSHIPS (Eloquent)

- Create a `HasTranslatedAttributes` trait providing
  `translated(string $base): string` which returns `{$base}_{locale}`, falling
  back to the other locale when empty. Every model with `*_dv`/`*_en` pairs uses
  it. Views call `$model->translated('name')`, never `$model->name_dv`.
- **User**: hasOne Guardian, hasOne Coach, hasOne Student, HasRoles (spatie),
  HasApiTokens (sanctum). Accessors `isGuardian()`, `isCoach()`, `isAdmin()`,
  `isStudent()`. `getAuthIdentifierName()` returns `username`.
- **Guardian**: belongsTo User; belongsToMany Student (withPivot relationship,
  is_primary, receives_alerts); hasMany AgreementSignature.
- **Coach**: belongsTo User; hasMany Squad (head_coach_id); hasMany
  TrainingSession.
- **Student**: belongsTo User (own login, nullable); belongsTo User (created_by);
  belongsToMany Guardian (withPivot as above); belongsToMany Squad (through
  squad_student, withPivot enrolled_on, left_on, is_active); hasMany Attendance;
  hasMany AgreementSignature; hasOne `activeEnrolment` (SquadStudent where
  is_active = true); accessor `age` from date_of_birth; accessor
  `hasSignedCurrentAgreement` (bool); accessor `primaryGuardian`.
- **Squad**: belongsTo Coach (head_coach_id); belongsToMany Student; hasMany
  TrainingSession; accessor `active_student_count`; uses the translation trait.
- **TrainingSession**: belongsTo Squad; belongsTo Coach; hasMany Attendance;
  belongsTo User (created_by); uses the translation trait.
- **Attendance**: belongsTo TrainingSession; belongsTo Student; belongsTo User
  (marked_by).
- **AgreementTemplate**: hasMany AgreementSignature; scope `current()`; uses the
  translation trait.
- **AgreementSignature**: belongsTo AgreementTemplate, Student, Guardian.
  Immutable after creation — see §8.3.
- **FrameworkPillar**, **FrameworkStrikeLevel**: reference models, scope
  `active()` ordered by sort_order, use the translation trait.

Casts: dates to `date`/`datetime`, json columns to `array`, enums to PHP 8.1
backed enums in `app/Enums/` (StudentStatus, SessionStatus, AttendanceStatus,
AgreementStatus, Locale). Every enum exposes `label(): string` returning a
translated string.

---

## 6. ROLES & PERMISSIONS (spatie, seeded)

Roles: `admin`, `coach`, `guardian`, `student`.

- **admin**: all permissions
- **coach**: `session.view`, `session.start`, `session.complete`,
  `attendance.mark`, `squad.view.own`, `student.view.own_squad`, `framework.view`
- **guardian**: `student.view.own`, `agreement.sign`, `session.view.own_child`,
  `attendance.view.own_child`, `framework.view`, `profile.edit.own`
- **student**: `schedule.view.own`, `attendance.view.own`, `framework.view`,
  `profile.edit.own` (limited to password and language only)

Admin permissions include: `student.manage`, `squad.manage`, `coach.manage`,
`guardian.manage`, `session.manage`, `agreement.template.manage`,
`framework.manage`, `credential.issue`, `report.view`, `audit.view`.

Role and permission display names are translated via lang keys, never shown raw.

---

## 7. ROUTES / ENDPOINTS

All authenticated routes carry `auth` plus role/permission middleware.
`SetLocale` runs on the entire `web` group. There is NO email verification and NO
registration route — Breeze's `register` routes and views are removed entirely.

### Public (no auth)
| Method | URI | Action |
|---|---|---|
| GET | `/` | `PublicController@home` |
| GET | `/locale/{locale}` | `LocaleController@switch` |
| GET | `/framework` | `PublicController@framework` (pillars + strikes, read-only) |
| GET | `/contact` | `PublicController@contact` (static info only) |

### Auth
| Method | URI | Action |
|---|---|---|
| GET/POST | `/login` | `AuthenticatedSessionController` (single `username` field) |
| POST | `/logout` | — |
| GET/POST | `/password/change` | `ForcePasswordChangeController` |

Password reset is ADMIN-DRIVEN only: there is no public forgot-password flow,
because students have no email and guardians may not either. Breeze's
forgot-password and reset-password routes and views are removed.

### Agreement gate (auth, role:guardian, password.changed)
| Method | URI | Action |
|---|---|---|
| GET | `/agreement/{student}` | `AgreementController@show` |
| POST | `/agreement/{student}` | `AgreementController@sign` |
| GET | `/agreement/{student}/download` | `AgreementController@download` (HTML print view in the signed locale; not PDF in Phase 1) |

### Guardian portal (auth, role:guardian, `agreement.signed`)
| Method | URI | Action |
|---|---|---|
| GET | `/portal` | `GuardianDashboardController@index` |
| GET | `/portal/children` | `GuardianStudentController@index` |
| GET | `/portal/children/{student}` | `GuardianStudentController@show` |
| GET | `/portal/schedule` | `GuardianScheduleController@index` |
| GET | `/portal/attendance/{student}` | `GuardianAttendanceController@index` |
| GET | `/portal/profile` | `ProfileController@edit` |
| PATCH | `/portal/profile` | `ProfileController@update` (includes locale) |

### Student portal (auth, role:student, `agreement.signed`) — read-only
| Method | URI | Action |
|---|---|---|
| GET | `/student` | `StudentDashboardController@index` |
| GET | `/student/schedule` | `StudentScheduleController@index` |
| GET | `/student/attendance` | `StudentAttendanceController@index` |
| GET | `/student/profile` | `StudentProfileController@edit` |
| PATCH | `/student/profile` | `StudentProfileController@update` (password + locale ONLY) |

### Coach portal (auth, role:coach)
| Method | URI | Action |
|---|---|---|
| GET | `/coach` | `CoachDashboardController@index` |
| GET | `/coach/squads` | `CoachSquadController@index` |
| GET | `/coach/squads/{squad}` | `CoachSquadController@show` |
| GET | `/coach/sessions` | `CoachSessionController@index` |
| GET | `/coach/sessions/{session}` | `CoachSessionController@show` |
| POST | `/coach/sessions/{session}/start` | `CoachSessionController@start` |
| POST | `/coach/sessions/{session}/complete` | `CoachSessionController@complete` |
| GET | `/coach/sessions/{session}/attendance` | `AttendanceController@edit` |
| POST | `/coach/sessions/{session}/attendance` | `AttendanceController@store` |

### Admin portal (auth, role:admin)
| Method | URI | Action |
|---|---|---|
| GET | `/admin` | `AdminDashboardController@index` |
| resource | `/admin/students` | `Admin\StudentController` (full) |
| POST | `/admin/students/{student}/status` | `Admin\StudentController@updateStatus` |
| POST | `/admin/students/{student}/guardians` | `Admin\StudentGuardianController@store` |
| DELETE | `/admin/students/{student}/guardians/{guardian}` | `Admin\StudentGuardianController@destroy` |
| POST | `/admin/students/{student}/credentials` | `Admin\StudentCredentialController@issue` |
| resource | `/admin/guardians` | `Admin\GuardianController` (full) |
| POST | `/admin/guardians/{guardian}/credentials` | `Admin\GuardianCredentialController@reset` |
| resource | `/admin/coaches` | `Admin\CoachController` (full) |
| POST | `/admin/coaches/{coach}/credentials` | `Admin\CoachCredentialController@reset` |
| resource | `/admin/squads` | `Admin\SquadController` (full) |
| POST | `/admin/squads/{squad}/enrol` | `Admin\SquadEnrolmentController@store` |
| DELETE | `/admin/squads/{squad}/enrol/{student}` | `Admin\SquadEnrolmentController@destroy` |
| resource | `/admin/sessions` | `Admin\SessionController` (full) |
| POST | `/admin/sessions/generate` | `Admin\SessionController@generateFromSchedule` |
| resource | `/admin/agreement-templates` | `Admin\AgreementTemplateController` (full) |
| POST | `/admin/agreement-templates/{t}/publish` | `Admin\AgreementTemplateController@publish` |
| GET | `/admin/agreements` | `Admin\AgreementSignatureController@index` |
| resource | `/admin/framework/pillars` | `Admin\PillarController` (index, edit, update) |
| resource | `/admin/framework/strike-levels` | `Admin\StrikeLevelController` (index, edit, update) |
| GET | `/admin/reports/attendance` | `Admin\ReportController@attendance` |
| GET | `/admin/audit` | `Admin\AuditController@index` |

### API (prefix `/api/v1`, sanctum)
For the Flutter app in a later phase. Build ONLY these endpoints now, returning
API Resources that resolve `*_dv`/`*_en` pairs to a single localised field based
on `Accept-Language`:

| Method | URI | Notes |
|---|---|---|
| POST | `/api/v1/auth/login` | username + password → token |
| POST | `/api/v1/auth/logout` | — |
| GET | `/api/v1/me` | includes role and `must_change_password` |
| GET | `/api/v1/children` | guardian only: students + agreement status |
| GET | `/api/v1/schedule` | upcoming sessions scoped to the caller's role |
| GET | `/api/v1/framework` | pillars + strike levels |

---

## 8. BUSINESS LOGIC RULES

All rules live in service classes under `app/Services/`, not in controllers.
Controllers validate request shape via FormRequests; services are the authority
on business rules. No business logic in Blade. No raw SQL.

### 8.1 Authentication

- The login form has ONE identifier field labelled with a translated string
  meaning "Index number or phone number", plus password.
- `AuthenticatedSessionController` resolves the user by `users.username` only. Do
  not attempt to guess whether the input is a phone or an index number — both
  live in the same unique `username` column.
- Normalise input before lookup: trim, strip spaces, uppercase.
- Reject login when `is_active` is false with a translated message.
- On successful login, update `last_login_at`.
- If `must_change_password` is true, ALL routes except `/password/change`,
  `/logout` and `/locale/*` redirect to the change-password screen. New password
  must differ from the current one, min 8 characters.
- Throttle: 5 failed attempts per username per minute.
- Sessions for `student` role users expire after 2 hours of inactivity; other
  roles use the default. Configured via `config/academy.php`.

### 8.2 Admin creation of records (replaces all self-registration)

There is no public application flow. Everything below is admin-only.

**Creating a student**
- Required: `index_number` (unique, format per §4), `full_name`, `date_of_birth`
  (age must resolve to 5-18 inclusive as of today — reject outside that range with
  a translated message), `gender`, `address`, and at least one guardian.
- The admin either links an EXISTING guardian (search by phone or name) or creates
  a new one inline. Exactly one linked guardian must be marked `is_primary`.
- Optional: `school_name`, `class_level`, photo.
- Student status starts as `pending`. `registered_at` = now, `created_by` = the
  acting admin.
- Creating the student does NOT automatically create their login. The admin issues
  credentials as a separate explicit action.
- Accept Thaana and Latin input in every free-text field. Do not apply any
  alphabetic-only validation rule that would reject Thaana characters.

**Issuing student credentials** (`StudentCredentialController@issue`)
- Creates a `users` row with `username` = the student's `index_number`, `name` =
  `full_name`, `phone` = null, role `student`, `must_change_password` = true, and
  links it via `students.user_id`.
- Generates a random 8-character password, displays it ONCE on screen for the
  admin to hand over on paper. Never emailed, never stored in plain text, never
  shown again, never written to logs or flashed beyond one response.
- Re-running the action on a student who already has a login RESETS the password
  (new random password shown once, `must_change_password` reset to true). It does
  not create a second account.
- If the student's `index_number` is later changed by an admin, the linked user's
  `username` updates in the same transaction.

**Creating a guardian**
- Required: name, phone (7 digits, `/^[79]\d{6}$/`), address, relationship to at
  least one student. Optional: email, national_id, occupation.
- Creates the `users` row with `username` = the phone number, role `guardian`,
  `must_change_password` = true, and a random 10-character password shown once.
- If a user with that phone already exists, link to it rather than creating a
  duplicate, and surface a translated notice to the admin.

**Creating a coach** — same pattern: `username` = phone, role `coach`,
auto-generated `coach_no`, password shown once.

### 8.3 Agreement gating (core Phase 1 rule)

- Middleware `agreement.signed` applies to BOTH the guardian portal and the
  student portal.
- A guardian may access `/agreement/*`, `/portal/profile` and `/locale/*` always.
  Every other `/portal/*` route is blocked while ANY of their linked students
  lacks a valid signature against the CURRENT agreement template. Blocked access
  redirects to `/agreement/{firstUnsignedStudent}` with a translated notice.
- A STUDENT whose own record lacks a valid current signature is blocked from every
  `/student/*` route except profile and locale, and sees a translated screen
  explaining that their guardian must complete the agreement first. The student
  can never sign it themselves — only a linked guardian can.
- **The gate fails CLOSED.** If no template is current, guardians and students are
  denied portal access and shown a translated message directing them to the
  academy office; admins retain access so they can publish one.
- The agreement routes require the guardian to have completed the forced password
  change — a binding agreement may not be signed while on an admin-issued
  password.
- The agreement screen renders the body in the ACTIVE locale, and the language
  toggle remains available on it so a guardian can read it in either language
  before signing.
- Signing requires: `signatory_name` free text, min 3 chars; every consent clause
  marked `required:true` must be checked; and either a typed name or a drawn
  signature PNG must be present.
- On signing, store `signed_locale` = active locale, `template_snapshot` = the
  body text in that locale, plus `ip_address` and `user_agent`.
- **Signatures are immutable after creation.** `template_snapshot`, `signed_at`,
  `signed_locale`, `consents` and `agreement_template_id` may never be updated —
  guard in the model's `updating` event. Corrections happen by REVOKING and
  re-signing; `status`, `revoked_at` and `revoked_reason` are writable only
  through `AgreementService::revoke()`.
- A student's status transitions `pending` → `active` automatically once a valid
  signature exists AND the student holds an active squad enrolment.
- If an admin publishes a NEW current template version, all existing signatures
  remain valid records but no longer satisfy the gate; guardians are prompted to
  re-sign. Never delete or mutate old signatures.

### 8.4 Enrolment

- A student may hold only one active squad enrolment. Enrolling into a second
  squad closes the previous enrolment (`left_on` = today, `is_active` = false) and
  records it in the activity log.
- Block enrolment if the squad is at capacity (when `capacity` is not null).
- Block enrolment if student status is `inactive`.
- A `suspended` student MAY remain enrolled but must not be markable as present.

### 8.5 Sessions & attendance

- `generateFromSchedule` creates planned sessions for a squad from its
  `training_days` + default times across an admin-supplied date range, skipping
  dates where a session already exists for that squad.
- `scheduled_end` must be after `scheduled_start`; a squad may not have two
  sessions that overlap in time — validate and reject.
- Attendance may only be marked when session status is `in_progress` or
  `completed`, and only by the assigned coach or an admin.
- The attendance roster is the set of students with an ACTIVE enrolment in that
  squad as of the session date. Students whose status is `suspended` appear on the
  roster visibly flagged and their status is forced to `excused` and locked — they
  cannot be marked `present` or `late`.
- Marking attendance is idempotent: re-submitting updates existing rows via
  `updateOrCreate` on (`training_session_id`, `student_id`).
- Sessions older than 14 days are read-only for coaches; admins may still edit.
- Cancelling a session requires `cancellation_reason` and deletes no attendance.

### 8.6 Child data protection (mandatory)

- Guardians may view ONLY students linked to them via `guardian_student`. Enforced
  with Policies (`StudentPolicy@view`), **not** query scoping alone. Query scoping
  without a policy is a failure of this requirement.
- Coaches may view ONLY students with an active enrolment in a squad they are
  assigned to.
- A student may view ONLY their own record. A student must never reach any
  listing, roster, or detail page containing another student's name, photo,
  attendance or status — including squad rosters. The student schedule shows
  session time, venue and coach only, never a teammate list.
- Every read of a student detail page by a coach or admin is written to the
  activity log with the causer.
- Student photos are stored on the `private` disk and served only through a
  signed, policy-checked route. Photo URLs in views must be generated as **signed**
  URLs (`URL::signedRoute` or equivalent) from a single shared construction site.
  Never expose a public storage URL or `Storage::url` for student media. Media
  load failures must be visibly handled with a placeholder, never hidden with
  `onerror` suppression.
- `RETENTION.md` (English) documents what is stored about minors, which roles can
  see each field, that minors hold their own login accounts and how those are
  issued and revoked, and that deletion is soft-delete with a documented purge
  path.

### 8.7 Attendance statistics — single authority

- `AttendanceStatisticsService` is the SOLE authority for any attendance count,
  rate or percentage. Every controller, report, dashboard and API resource calls
  it. No percentage is computed anywhere else — not in a controller, not in a
  Blade file, not in a query scope.
- All roles must see an identical figure for the same student over the same
  period.
- The treatment of `late` and `excused` is a business rule read from
  `config('academy.attendance')` with documented keys, not hardcoded.

### 8.8 Framework reference

- `/framework` renders pillars and strike levels from the database, ordered by
  `sort_order`, in the active locale, read-only for the public.
- The strike ladder is entirely table-driven. No controller, service, or Blade
  file may hardcode the number of strike levels, the timeout duration, or which
  level triggers suspension. Read the trigger level from
  `config('academy.suspension_trigger_level')`, which reads
  `env('ACADEMY_SUSPENSION_TRIGGER_LEVEL', 3)`.

---

## 9. UI SCREENS

Tailwind, mobile-first (most users are on phones), correct in BOTH directions —
every screen verified in `dv` (RTL) and `en` (LTR). Palette: deep navy `#0B1F3A`
and gold `#C9A227` to match the academy crest. Shared `layouts/app.blade.php`,
`layouts/public.blade.php`, and Blade components in
`resources/views/components/`, including `<x-lang-switcher />` used in both
layouts.

### Public
1. **Home** — academy intro, the 4 pillars as icon cards, link to the framework
   guide, contact block, and a translated note that enrolment is arranged through
   the academy office (NO registration form, no sign-up link).
2. **Framework Guide** — the 4 pillars with full descriptions; the strike ladder
   as a responsive table (level, type, action taken, parent's role); collapses to
   stacked cards on mobile.
3. **Contact** — academy address, phone, office hours. Static.

### Auth
4. **Login** — single identifier field (index number or phone), password, language
   toggle. No "register" or "forgot password" links.
5. **Forced password change** — shown on first login for every account type.

### Student portal
6. **Dashboard** — own name, index number, photo, squad, status badge, next
   session, attendance percentage this month. Nothing about any other student.
7. **Schedule** — own upcoming sessions grouped by date (time, venue, coach).
8. **Attendance** — own attendance history with month filter and a summary bar.
9. **Profile** — password change and language only; everything else read-only.

### Guardian portal
10. **Agreement signing screen** — full agreement body scrollable, language toggle
    visible, a checkbox per consent clause, typed-name input, a canvas signature
    pad (Alpine + plain JS, no library), submit disabled until all required
    clauses are checked. Shows which child is being signed for.
11. **Dashboard** — cards per child: photo, name, index number, squad, status
    badge, next session, attendance percentage this month.
12. **Child detail** — profile summary, enrolment history, attendance history with
    month filter.
13. **Schedule** — upcoming sessions for all linked children, grouped by date.
14. **Profile** — name, phone, email, address, password change, language.

### Coach portal
15. **Dashboard** — today's sessions with a start button, assigned squads, total
    students.
16. **Squad list / squad detail** — roster with photos, index numbers, age, status
    badges.
17. **Session detail** — squad, time, venue, status, start/complete actions.
18. **Attendance marking** — large tap targets, one row per student with
    present/absent/late/excused segmented control, suspended students visibly
    locked and greyed with a translated reason tooltip, sticky save bar showing
    counts.

### Admin portal
19. **Dashboard** — counts (active students, students without a login issued,
    students without a signed agreement, active squads, sessions today),
    attendance rate this week, recent activity feed.
20. **Students** — searchable index (matches index number, name in Thaana or
    Latin, guardian phone), filters by status / squad / agreement signed / login
    issued. Create and edit forms with an inline guardian linking panel.
21. **Student detail** — profile, guardians, enrolment, agreement status,
    attendance summary, and a credentials panel with an "issue / reset login"
    button that reveals the generated password once in a modal with a copy button
    and a translated warning that it will not be shown again.
22. **Guardians / Coaches** — CRUD indexes and forms, each with the same credential
    reset panel.
23. **Squads** — CRUD with paired dv/en name and venue inputs, enrolment panel with
    add/remove students.
24. **Sessions** — list view by squad and date range, CRUD, and a "generate
    sessions" modal (squad + date range).
25. **Agreement templates** — list of versions, editor with side-by-side dv/en body
    fields and a clause repeater carrying both labels, "publish as current" with a
    confirmation warning that guardians will be required to re-sign; publish
    blocked if either language is incomplete.
26. **Signatures register** — who signed what version, in which language, and when,
    with a view of the stored snapshot.
27. **Framework management** — edit pillar and strike level content with paired
    dv/en fields.
28. **Attendance report** — filter by squad + date range, per-student counts and
    percentage (via `AttendanceStatisticsService`), CSV export with translated
    headers and a UTF-8 BOM so Thaana opens correctly in Excel.
29. **Audit log** — paginated, with causer, subject, translated action label, date.

---

## 10. OUT OF SCOPE FOR PHASE 1 — DO NOT BUILD

Public or self-service registration of any kind; behaviour check-ins and pillar
ratings; school report card uploads; badges, rewards and MVP-of-the-week; the
live strike logging engine and its push alerts; time-out timers; the parent
"behaviour flag" button; in-app chat; the meeting scheduler; the Flutter mobile
app; PDF generation; SMS gateway; push notifications.

Do not create migrations, models, routes, or UI for any of these. The reference
tables `framework_pillars` and `framework_strike_levels` ARE in scope — as
read-only reference content only.

---

## 11. DELIVERABLES (file/folder structure)

```
app/
  Concerns/HasTranslatedAttributes.php
  Enums/{StudentStatus,SessionStatus,AttendanceStatus,AgreementStatus,Locale}.php
  Http/Controllers/{Public,Admin,Coach,Guardian,Student,Api/V1}/...
  Http/Controllers/{LocaleController,ForcePasswordChangeController}.php
  Http/Middleware/{SetLocale,EnsureAgreementSigned,EnsurePasswordChanged}.php
  Http/Requests/...            (one FormRequest per write action)
  Http/Resources/Api/V1/...
  Models/...
  Policies/{StudentPolicy,SquadPolicy,TrainingSessionPolicy,AttendancePolicy,UserPolicy}.php
  Services/{StudentRegistrationService,CredentialService,AgreementService,
            EnrolmentService,SessionSchedulingService,AttendanceService,
            AttendanceStatisticsService}.php
  Support/FormatsDates.php
config/academy.php
database/migrations/...
database/seeders/{RolePermissionSeeder,FrameworkPillarSeeder,StrikeLevelSeeder,
                  AgreementTemplateSeeder,AdminUserSeeder,DemoDataSeeder}.php
database/factories/...
lang/dv/*.php, lang/en/*.php
resources/fonts/             (licensed Thaana webfont)
resources/views/{layouts,components,public,auth,student,guardian,coach,admin}/...
routes/{web.php,api.php}
tests/Feature/..., tests/Unit/...
README.md, RETENTION.md, SPEC.md, .env.example
```

---

## 12. CONSTRAINTS

- PSR-12 throughout. Laravel Pint clean.
- Strict types where practical; typed properties, typed method signatures, return
  types on every method.
- Breeze's register, forgot-password, reset-password and email verification
  routes, controllers AND views are deleted. `php artisan route:list` must show no
  route named `register`, `password.request` or `password.email`.
- No placeholder or lorem ipsum content in the application itself. Seeders provide
  realistic Maldivian demo data (Dhivehi names in Thaana, 7-digit phone numbers
  starting 7 or 9, index numbers in a plausible format, Hulhumalé/Malé addresses).
  Demo data lives ONLY in `DemoDataSeeder` and is excluded from production seeding.
- Generated passwords are never logged, never emailed, never persisted in plain
  text, and appear exactly once in an HTTP response.
- No business logic in Blade templates. No raw SQL. No N+1 queries — eager load and
  assert it in tests.
- Every user-facing string passes through `__()`. Every content field renders
  through the translation trait.
- No directional Tailwind utilities (`ml-*`, `mr-*`, `pl-*`, `pr-*`, `text-left`,
  `text-right`) in mirrored layout — use logical properties.
- `.env.example` fully updated, including `APP_LOCALE=dv`,
  `APP_FALLBACK_LOCALE=en`, `ACADEMY_NAME`,
  `ACADEMY_SUSPENSION_TRIGGER_LEVEL=3`, `ACADEMY_STUDENT_SESSION_LIFETIME=120`,
  `FILESYSTEM_DISK`, and the private disk config for student photos and
  signatures.
- `README.md` (English) documents: local setup, seeding, default admin
  credentials, the admin workflow for adding a student and issuing their login,
  how to add or change a translation key, how to change the strike ladder, and
  deployment for CyberPanel / OpenLiteSpeed via Git + SSH.
- No package outside §2 without a stated justification in `README.md`.
- Where source documents were unreadable, seed `[DV CONTENT PENDING]` /
  `[EN CONTENT PENDING]`. Do not fabricate or machine-translate content.

---

## 13. DEFINITION OF DONE

1. `composer install && php artisan migrate:fresh --seed` runs clean on MySQL 8.
2. `npm install && npm run build` succeeds.
3. `php artisan test` passes with, at minimum, these named tests — each must
   genuinely exercise the rule, not merely assert `assertStatus(200)`:

**Authentication**
- `AuthenticationTest::test_student_can_log_in_with_index_number`
- `AuthenticationTest::test_guardian_can_log_in_with_phone_number`
- `AuthenticationTest::test_index_number_lookup_is_case_and_space_insensitive`
- `AuthenticationTest::test_inactive_user_cannot_log_in`
- `AuthenticationTest::test_first_login_forces_password_change`
- `AuthenticationTest::test_no_public_registration_route_exists`

**Student creation**
- `StudentCreationTest::test_admin_can_create_student_with_primary_guardian`
- `StudentCreationTest::test_student_creation_requires_at_least_one_guardian`
- `StudentCreationTest::test_duplicate_index_number_is_rejected`
- `StudentCreationTest::test_age_outside_5_to_18_is_rejected`
- `StudentCreationTest::test_thaana_input_is_accepted_and_stored_intact`
- `StudentCreationTest::test_non_admin_cannot_create_a_student`

**Credentials**
- `CredentialTest::test_issuing_login_sets_username_to_index_number`
- `CredentialTest::test_reissuing_resets_password_without_creating_second_account`
- `CredentialTest::test_changing_index_number_updates_linked_username`
- `CredentialTest::test_generated_password_is_not_persisted_in_plain_text`

**Agreement gate**
- `AgreementGateTest::test_guardian_is_redirected_to_agreement_before_portal_access`
- `AgreementGateTest::test_student_is_blocked_until_guardian_signs`
- `AgreementGateTest::test_student_cannot_sign_own_agreement`
- `AgreementGateTest::test_new_template_version_forces_resign`
- `AgreementGateTest::test_signature_stores_immutable_template_snapshot`
- `AgreementGateTest::test_snapshot_matches_the_locale_the_guardian_signed_in`
- `AgreementGateTest::test_gate_denies_when_no_current_template_exists`
- `AgreementGateTest::test_admin_retains_access_when_no_current_template_exists`
- `AgreementGateTest::test_guardian_must_change_password_before_signing`
- `AgreementSignatureTest::test_signed_agreement_fields_cannot_be_updated`
- `AgreementSignatureTest::test_revocation_is_the_only_mutation_path`
- `AgreementSignatureTest::test_revoked_signature_no_longer_satisfies_the_gate`
- `AgreementTemplateTest::test_publish_blocked_when_either_language_incomplete`

**Enrolment, sessions, attendance**
- `EnrolmentTest::test_new_enrolment_closes_previous_active_enrolment`
- `EnrolmentTest::test_enrolment_blocked_when_squad_at_capacity`
- `AttendanceTest::test_only_assigned_coach_or_admin_can_mark_attendance`
- `AttendanceTest::test_suspended_student_cannot_be_marked_present`
- `AttendanceTest::test_marking_attendance_twice_updates_not_duplicates`
- `SessionSchedulingTest::test_generate_skips_dates_with_existing_sessions`
- `SessionSchedulingTest::test_overlapping_session_is_rejected`

**Attendance statistics**
- `AttendanceStatisticsTest::test_all_roles_see_identical_percentage_for_same_student`
- `AttendanceStatisticsTest::test_late_treatment_follows_config`
- `AttendanceStatisticsTest::test_excused_is_excluded_from_denominator`
- `AttendanceStatisticsTest::test_no_percentage_is_computed_outside_the_service`

**Authorisation — must assert on HTTP responses, not on `->can()` alone**
- `AuthorizationTest::test_guardian_cannot_view_another_guardians_child`
- `AuthorizationTest::test_coach_cannot_view_student_outside_own_squad`
- `AuthorizationTest::test_student_cannot_view_another_students_record`
- `AuthorizationTest::test_student_schedule_exposes_no_teammate_names`
- `RouteCoverageTest::test_every_non_public_route_rejects_guests_and_wrong_roles`

**Student media**
- `StudentPhotoTest::test_signed_photo_url_renders_for_authorised_viewer`
- `StudentPhotoTest::test_unsigned_photo_url_is_rejected`
- `StudentPhotoTest::test_guardian_cannot_load_another_childs_photo`
- `StudentPhotoTest::test_photo_route_is_not_publicly_reachable`

**API**
- One happy-path and one wrong-role test per `/api/v1` endpoint in §7
- `ApiLocaleTest::test_accept_language_switches_resource_language`

**Localisation**
- `LocalisationTest::test_dhivehi_locale_renders_rtl_direction`
- `LocalisationTest::test_english_locale_renders_ltr_direction`
- `LocalisationTest::test_switching_locale_persists_for_authenticated_user`
- `LocalisationTest::test_invalid_locale_is_rejected`
- `LocalisationTest::test_lang_dv_and_en_have_identical_key_sets` — recursively
  diffs every file in `lang/dv` against `lang/en`, failing on any missing or extra
  key in either direction
- `LocalisationTest::test_no_untranslated_literals_in_blade_views` — scans
  `resources/views` for user-facing text nodes outside `__()`/`@lang`
- `LocalisationTest::test_thaana_font_class_applies_only_in_dv_locale`
- `LocalisationTest::test_no_proprietary_font_binary_in_repository`

4. Every route in §7 resolves and returns 200/302 as appropriate for an authorised
   user, and 403 for an unauthorised one, in BOTH locales.
5. `php artisan route:list` shows no route missing its intended middleware, and no
   registration or public password-reset route.
6. `./vendor/bin/pint --test` reports no fixable issues.
7. No `dd()`, `dump()`, `var_dump()`, or commented-out code anywhere.
8. Any rule in this document that could not be implemented as specified is
   reported explicitly, with the reason. Silent deviation is not acceptable, and a
   definition-of-done item that is partially met is reported as unmet.

---

## 14. OPEN ITEMS (blocking later phases, not Phase 1 acceptance)

- **Strike levels 4 and 5** — definitions not yet supplied by the customer. Seed as
  `[DV CONTENT PENDING]` / `[EN CONTENT PENDING]`.
- **Strike consequence table** — the full "action taken" and "parent's role"
  columns from the source document are not yet transcribed. Seed as pending.
- **Licensed Thaana webfont** — must be procured before go-live. See
  `FONT-LICENCE.md`.
- **`late` attendance treatment** — whether `late` counts as attended is an academy
  policy decision, currently config-driven with a default.
- **Index number origin** — confirm whether this is an academy-issued number or a
  school exam index (the latter can repeat across schools and change yearly, and
  would need scoping to remain unique).
