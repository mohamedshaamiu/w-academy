# W-ACADEMY — PHASE 1 CODEBASE AUDIT

> **The codebase boots, migrates, seeds and passes 100% of its own test suite (43/43, 112 assertions) — but the suite does not cover the two features that are completely non-functional in it: student photo delivery (every photo returns 403) and the entire `/api/v1` surface (500s on the first call).**

| | |
|---|---|
| **Audited commit** | `46a6336885a8a924a065a360f2bff34d5f4158df` — *"Initial Phase 1 build: W-Academy bilingual management system"* (the only commit in the repository) |
| **Audit branch** | `audit/phase-1`, created from `main` at that SHA |
| **Date** | 2026-08-11 |
| **Files created by this audit** | `AUDIT.md`, `AUDIT-EVIDENCE/` (19 logs). Zero application files created, modified or deleted — proof in §8 |

---

## 0. DECLARED DEVIATIONS FROM THE AUDIT PROMPT

Per the prompt's §8 reporting standard, every deviation is declared here rather than absorbed silently.

**D1 — `SPEC.md` does not exist. This is the single most important finding in this document.**

The prompt names `SPEC.md` as "the full Phase 1 specification and the sole acceptance contract". It is not in the working tree, and it has never been tracked:

```
$ find . -iname "SPEC*.md" -o -iname "CLAUDE*.md"   (excluding vendor/, node_modules/)
(no results)

$ git log --all --pretty=format: --name-only --diff-filter=A | sort -u | grep -i -E "spec|claude"
(no results)
```

`CLAUDE.md` is likewise absent (`.claude/` is gitignored and contains only `scheduled_tasks.lock`). `README.md:8` confirms the contract lived outside the repo: *"See the project brief for the full Phase 1 scope and the out-of-scope list for later phases."* The brief was never committed.

I raised this and was directed to **reconstruct an implied spec and audit against that**. §3 is therefore audited against an **INFERRED-SPEC** derived from `README.md`, `RETENTION.md`, `config/academy.php`, the `lang/` domain files, the existing test names, and the requirements the audit prompt itself restates (§4.1–4.8). It is labelled `IS-n.n` throughout, **never** `SPEC.md §n`, so no reader mistakes it for the contract.

**What this costs you, stated plainly:** a PASS in §3 means "this matches the behaviour I reconstructed", not "this satisfies the contract". Any requirement in the real SPEC.md that left no trace in the repo is invisible to me and appears nowhere below — including, critically, **the out-of-scope list**, which makes §4.7 largely unanswerable. Re-run §3 against the real SPEC.md before making an acceptance decision.

**D2 — §4.7 (scope violations) is answered as UNVERIFIABLE rather than skipped.** Determining what was built outside scope requires the out-of-scope list, which lives only in the missing brief. I inventory the strongest candidate and state what I cannot conclude.

**D3 — §4.1's "every `*_dv`/`*_en` column pair specified in SPEC.md §4" is checked for internal consistency only.** I verified that every bilingual pair the code references exists in the migrations and that views read them through the translation trait. I cannot verify the *set* of pairs is the set the spec demanded.

**D4 — Retracted counts are retained.** Two blade-sweep counts in §4.1 were wrong and are struck through with the reason, per the prompt's instruction not to overwrite silently.

**D5 — Load-scale N+1 numbers are floors, not stress measurements.** `DemoDataSeeder` produces 4 students, 2 squads, 2 sessions, 4 attendances, and the test guardian has 1 child. I report measured counts at that volume plus the structural per-iteration analysis, and do not extrapolate a number I did not observe.

**D6 — `migrate:fresh --seed` ran against a scratch database** (`wacademy_audit_scratch`, created for this audit) via a `DB_DATABASE` env override. The development database `wacademy` was never dropped or modified.

---

## 1. GROUND TRUTH (§2)

Raw output for every command is in `AUDIT-EVIDENCE/`. Nothing below is summarised from memory.

| # | Command | Result | Evidence |
|---|---|---|---|
| 1 | `git log --oneline -20` / `git status` | 1 commit; working tree clean at start | `01-git.log` |
| 2 | `composer install` | **PASS** — exit 0, 85 packages | `02-composer-install.log` |
| 3 | `npm install && npm run build` | **PASS** — exit 0, 57 modules, built in 9.57s | `03-npm-install-build.log` |
| 4 | `php artisan migrate:fresh --seed` | **PASS** — exit 0, 19 migrations, 6 seeders | `04-migrate-fresh-seed.log` |
| 5 | `php artisan test` | **PASS** — exit 0, `Tests: 43 passed (112 assertions)`, 0 failed, 0 skipped, 0 incomplete | `05-artisan-test.log`, `05b-phpunit-testdox.log` |
| 6 | `php artisan route:list --json` | 106 routes | `06-route-list.json`, `06-route-list.txt` |
| 7 | `./vendor/bin/pint --test` | **PASS** — `{"tool":"pint","result":"passed"}`, exit 0 | `07-pint-test.log` |
| 8 | `php artisan about` | Laravel 11.55.0, PHP 8.2.12, locale `dv`, MySQL, `APP_DEBUG=ENABLED` | `08-artisan-about.log` |
| 9 | File counts | 221 files in `app`+`resources/views`+`database` | `09-file-inventory.log` |

**None of steps 2–5 failed.** The audit proceeded dynamically as well as statically.

Per-directory breakdown (`09-file-inventory.log`): 41 controllers, 13 models, 5 policies, 6 services, 5 enums, 19 migrations, 7 seeders, 8 factories, 5 middleware, 18 FormRequests, 16 `lang/dv` + 16 `lang/en` files, ~90 Blade views, 10 test files. **`tests/Unit` contains 0 files.**

Build note: `03-npm-install-build.log` shows the bundled webfont is `assets/MVBoli-DGgN0rby.ttf` — **MV Boli, not MV Faseyha**. See §4.2.

---

## 2. THE INFERRED SPEC

Reconstructed as described in D1. Sections are numbered to parallel what the prompt expects to find in SPEC.md §§3–9, 11–13. **This is not the contract.**

- **IS-3 Authentication** — username-based login (no email), students by index number, guardians/coaches by phone; no public registration; no self-service password reset; forced password change on first login; inactive accounts blocked.
- **IS-4 Data model** — bilingual `*_dv`/`*_en` content columns; students/guardians/coaches/squads/sessions/attendance/agreements/framework.
- **IS-5 Localisation** — full dv/en parity; `dv` default; RTL for Dhivehi; Thaana webfont; no hardcoded user-facing English.
- **IS-6 Admin workflows** — admin-only creation of students/guardians/coaches; credentials issued explicitly and shown once; squad enrolment; session scheduling; attendance.
- **IS-7 The agreement gate** — guardian must sign the current discipline agreement before portal access; students blocked until their guardian signs; students may never sign; new template version invalidates prior signatures; immutable snapshot at signing.
- **IS-8 Layering & authorisation** — business rules in services, not controllers or Blade; Policies (not query scoping) enforce access to another person's data.
- **IS-9 Child data protection** — photos on a private disk behind a policy-checked route; student portal exposes no teammate data.
- **IS-10 Out of scope** — *unrecoverable; the list existed only in the missing brief.*
- **IS-11 Framework reference** — read-only, table-driven strike ladder and pillars, no hardcoded level counts.
- **IS-12 Audit & retention** — activity logging of sensitive views; documented retention/purge path.
- **IS-13 Required tests** — the 10 test classes present in `tests/Feature` are treated as the named-test list, since the real names are unrecoverable.

---

## 3. REQUIREMENT-BY-REQUIREMENT VERDICT

Verdicts against **INFERRED-SPEC**, not against SPEC.md. No row is blank.

### IS-3 — Authentication

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-3.1 | Login resolves users by `users.username` | **PASS** | `app/Models/User.php:47-50` `getAuthIdentifierName(): 'username'`; `app/Http/Requests/Auth/LoginRequest.php:38-40`; schema `users.username varchar(30) UNIQUE` (`17-schema.log`) | — |
| IS-3.2 | Student authenticates with index number | **PASS** | `app/Services/CredentialService.php:26,35` sets `username = $student->index_number`; test `test_student_can_log_in_with_index_number` | — |
| IS-3.3 | Index lookup case/space-insensitive | **PASS** | `LoginRequest.php:28-31` `strtoupper(trim(preg_replace('/\s+/','',…)))`; test `test_index_number_lookup_is_case_and_space_insensitive` | Same normalisation is applied to guardian/coach phone usernames; harmless for digits |
| IS-3.4 | Breeze register routes removed | **PASS** | `06-route-list.txt` — grep for `register\|password\|verif\|confirm` returns only `password/change` (the forced-change route) | — |
| IS-3.5 | Breeze register/reset/verify **controllers** removed | **PASS** | `app/Http/Controllers/Auth/` contains only `AuthenticatedSessionController.php`; grep for `RegisteredUserController\|PasswordResetLinkController\|NewPasswordController\|EmailVerification\|ConfirmablePasswordController` across `app/ resources/ routes/ database/` → 0 hits | All three layers checked, per prompt |
| IS-3.6 | Breeze register/reset/verify **views** removed | **PASS** | `resources/views/auth/` contains only `change-password.blade.php`, `login.blade.php` | — |
| IS-3.7 | Generated password never logged/mailed/persisted plaintext/re-shown | **PASS** | `CredentialService.php:22,45,87,113,127,153` — returns plaintext only; `StudentCredentialController.php:17-23` passes it straight to a view; no `Log::`, no `Mail::`, no `->with('password'` anywhere; `12-app-hardcoded-strings.log`. Test `test_generated_password_is_not_persisted_in_plain_text` | Never enters the session; single response body only |
| IS-3.8 | Inactive users cannot log in | **PASS** | `LoginRequest.php:48-54`; test `test_inactive_user_cannot_log_in` | Check runs *after* `Auth::attempt` succeeds, then logs out — works, but the session is briefly established |
| IS-3.9 | `must_change_password` gates every route it should | **FAIL** | `18-runtime-probes.log` Probe D. `EnsurePasswordChanged` is on the guardian, student, coach and admin groups — but **not** on `agreement.show` / `agreement.sign` / `agreement.download` (`routes/web.php:57-61`), nor `students.photo` (`:154-156`), nor `dashboard.redirect` (`:51-53`) | A guardian still on the admin-issued password can sign the binding agreement. **P1-3** |
| IS-3.10 | API login enforces the same auth rules | **FAIL** | `app/Http/Controllers/Api/V1/AuthController.php:14-40` — no `RateLimiter`, no `must_change_password` check, no agreement gate. Contrast `LoginRequest.php:61-74` (5-attempt throttle) | **P1-7**, **P1-8** |

### IS-4 — Data model

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-4.1 | Bilingual `*_dv`/`*_en` pairs exist in migrations | **PASS** | `17-schema.log`: `squads.name_dv/en`, `squads.venue_dv/en`, `training_sessions.venue_dv/en`, `agreement_templates.title_dv/en`+`body_dv/en`, `framework_pillars.name_dv/en`+`description_dv/en`, `framework_strike_levels.label_dv/en`+`type_dv/en`+`action_dv/en`+`parent_role_dv/en` — every pair complete, no orphan single-language column | Verified complete *as a set internally*; see D3 |
| IS-4.2 | Views render via the translation trait, not direct `_dv`/`_en` | **PASS** | `translated()` used 61× in views; the 26 direct-access hits are all in admin create/edit forms and `agreement-templates/show.blade.php` (the deliberate side-by-side bilingual review page) — see §4.1 | — |
| IS-4.3 | Schema integrity: FKs, indexes, soft deletes | **PARTIAL** | `17-schema.log`. `users`, `students` have `deleted_at`; FKs and status indexes present. **Missing uniqueness:** `attendances(training_session_id, student_id)`, `guardian_student(guardian_id, student_id)`, `agreement_signatures(student_id, agreement_template_id)`, and no constraint limiting `squad_student` to one `is_active` row per student | All four invariants are enforced in PHP only. **P2-16** |
| IS-4.4 | `personal_access_tokens` for the Sanctum API | **ABSENT** | `18-runtime-probes.log` Probe B — table absent from the migrated scratch schema; no sanctum migration in `database/migrations/` | **P0-2** |

### IS-5 — Localisation

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-5.1 | `lang/dv` and `lang/en` key sets identical | **PASS** | `10-lang-key-diff.log`: DV 570 keys, EN 570 keys, 0 missing in each direction | Genuinely verified by recursive diff, not by file count |
| IS-5.2 | `lang/dv/validation.php` fully translated, not an English copy | **PASS** | `13-validation-lang.log` — real Thaana for `required`, `email`, `max` (all 4 sub-keys), `unique`, `confirmed`, `date`, `integer`; 25 translated `attributes`; 4 `custom` entries | Explicitly checked because the prompt flags it as a common shortcut |
| IS-5.3 | No hardcoded user-facing text in Blade | **PASS** | `11-blade-hardcoded-text.log` — quote-aware sweep: **0** untranslated Latin text nodes across ~90 views | Two earlier counts retracted — see §4.1 |
| IS-5.4 | No hardcoded English in controllers/services/enums/requests/exceptions | **PASS** | `12-app-hardcoded-strings.log` — all 51 `withMessages`/`->with('status'` payloads route through `__()`; enum `label()` methods call `__()`; 0 multi-word Latin sentence literals in `app/` | — |
| IS-5.5 | Dhivehi content complete | **FAIL** | `10-lang-key-diff.log`: `public.contact.address_value` and `public.contact.phone_value` both = `[DV CONTENT PENDING]` | Placeholder text ships to the public contact page. **P1-11** |
| IS-5.6 | Locale switching persists | **PASS** | `app/Http/Middleware/SetLocale.php:12-28`; test `test_switching_locale_persists_for_authenticated_user`, `test_invalid_locale_is_rejected` | — |

### IS-6 — Admin workflows

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-6.1 | Admin-only student creation | **PASS** | `StoreStudentRequest.php:15-18` `authorize()` → `isAdmin()`; `routes/web.php:110` `role:admin`; test `test_non_admin_cannot_create_a_student` | — |
| IS-6.2 | At least one guardian, exactly one primary | **PASS** | `app/Services/StudentRegistrationService.php:28-36`; tests `test_student_creation_requires_at_least_one_guardian`, `test_admin_can_create_student_with_primary_guardian` | Correctly in the service layer |
| IS-6.3 | Age 5–18 enforced | **PASS** | `StoreStudentRequest.php:41-49` via `StudentRegistrationService::ageAsOfToday`; test `test_age_outside_5_to_18_is_rejected` | — |
| IS-6.4 | Duplicate index number rejected | **PASS** | `StoreStudentRequest.php:22` `Rule::unique`; DB `students.index_number UNIQUE`; test `test_duplicate_index_number_is_rejected` | Enforced at both layers |
| IS-6.5 | Creating a student never auto-creates a login | **PASS** | `StudentRegistrationService.php` never calls `CredentialService`; `students.user_id` nullable (`17-schema.log`) | — |
| IS-6.6 | Re-issuing resets password, no second account | **PASS** | `CredentialService.php:24-43` — `if ($student->user) { update } else { create }`; test `test_reissuing_resets_password_without_creating_second_account` | — |
| IS-6.7 | Index-number change syncs username | **PASS** | `CredentialService.php:53-58`; `StudentController.php:117-119`; test `test_changing_index_number_updates_linked_username` | — |
| IS-6.8 | Coach numbers unique and stable | **FAIL** | `CredentialService.php:164-169` — `nextCoachNumber()` computes `(int) Coach::query()->max('id') + 1` outside any lock | Two concurrent creates yield the same `coach_no`; the `UNIQUE` index then throws a raw 500. Also collides after a delete. **P2-14** |
| IS-6.9 | Enrolment closes previous, respects capacity | **PASS** | `app/Services/EnrolmentService.php:17-24`; tests `test_new_enrolment_closes_previous_active_enrolment`, `test_enrolment_blocked_when_squad_at_capacity` | — |
| IS-6.10 | Session generation skips existing, rejects overlap | **PASS** | `app/Services/SessionSchedulingService.php:25,77,90`; tests `test_generate_skips_dates_with_existing_sessions`, `test_overlapping_session_is_rejected` | — |
| IS-6.11 | Attendance idempotent; suspended students excluded | **PASS** (app layer) | `app/Services/AttendanceService.php:33`; tests `test_marking_attendance_twice_updates_not_duplicates`, `test_suspended_student_cannot_be_marked_present` | Idempotence is application-level only — no DB unique key (IS-4.3) |

### IS-7 — The agreement gate

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-7.1 | Gate applied to **both** guardian and student groups | **PASS** | `18-runtime-probes.log` Probe D — `EnsureAgreementSigned` present on `portal` (`routes/web.php:65`) and `student` (`:80`) groups. Verified from the resolved middleware stack, not the filename | — |
| IS-7.2 | Publishing a new version invalidates the gate | **PASS** | `AgreementService.php:28-31` flips `is_current`; `Student.php:92-104` keys on `agreement_template_id = current->id`; test `test_new_template_version_forces_resign` | — |
| IS-7.3 | `template_snapshot` stored at signing time | **PASS** | `AgreementService.php:101` — written inside the `DB::transaction` at `create()` | — |
| IS-7.4 | `template_snapshot` **immutable thereafter** | **FAIL** | `18-runtime-probes.log` Probe C — `update(['template_snapshot'=>'TAMPERED'])` succeeded; `signed_at` and `status` also rewritable. `AgreementSignature.php:19-34` lists all three in `$fillable` with no guard | The one test named for this only proves decoupling from the template. **P1-4** |
| IS-7.5 | `signed_locale` matches the snapshot language | **PASS** | `AgreementService.php:68,100-101` — one `$locale` drives both fields; test `test_snapshot_matches_the_locale_the_guardian_signed_in` | Snapshot stores `body_*` only — not `title_*`, not the consent-clause labels the guardian actually agreed to. Partial record. **P2-25** |
| IS-7.6 | A student cannot sign their own agreement by any route | **PASS** | Probe D — all three `agreement.*` routes carry `RoleMiddleware:guardian`; `AgreementController.php:21,32` additionally `abort_unless` the student is linked to *that* guardian | Route-level + object-level. Solid |
| IS-7.7 | Gate cannot be bypassed | **FAIL** | Two holes: (a) `EnsureAgreementSigned.php:36-40` — if no template is `is_current`, the middleware **fails open** and every portal route is reachable; (b) Probe D — `/api/v1/children` and `/api/v1/schedule` carry no agreement middleware at all | (a) **P1-5**, (b) **P1-6** |
| IS-7.8 | Signing requires all mandatory consent clauses | **PASS** | `AgreementService.php:70-77` | — |
| IS-7.9 | Publication blocked unless both languages complete | **PASS** | `AgreementService.php:20-51`; tests `test_publish_blocked_when_either_language_incomplete`, `test_publish_succeeds_when_both_languages_complete` | Checks titles, bodies **and** every clause label in both languages |

### IS-8 — Layering & authorisation

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-8.1 | Business rules live in services | **PASS** | 6 services hold enrolment, scheduling, attendance, registration, credentials and agreement rules; all 11 `ValidationException::withMessages` domain-rule throws are in `app/Services/` (`12-app-hardcoded-strings.log`) | Genuinely good separation — the strongest part of this build |
| IS-8.2 | No business logic in controllers | **PARTIAL** | 4 controllers compute an attendance percentage inline: `AdminDashboardController.php:20`, `ReportController.php:28-35`, `GuardianDashboardController.php:34`, `StudentDashboardController.php:30` | And they **disagree** — see IS-8.6 |
| IS-8.3 | No business logic in Blade | **PARTIAL** | `coach/attendance-mark.blade.php:3-9,45-46,69` computes default attendance status from `$student->status->value === 'suspended'` in an `@php` block; `guardian/agreement-download.blade.php:24-25` performs `Storage::disk('local')->exists()` + `->get()` + `base64_encode` in the view; 15 `@php` blocks total | Disk I/O in the view layer. **P2-18**, **P2-19** |
| IS-8.4 | No raw SQL | **PASS** | Sweep for `DB::raw\|selectRaw\|whereRaw\|orderByRaw\|havingRaw\|DB::statement\|DB::select` across `app/ database/ resources/` → **0 hits** | — |
| IS-8.5 | Policies (not query scoping) protect other people's data | **PARTIAL** | See §4.4 trace table. Guardian→child detail and attendance are Policy-blocked (`GuardianStudentController.php:21`, `GuardianAttendanceController.php:14`); coach→squad/session/attendance are Policy-blocked. Guardian→children **index** and dashboard are scoping-only; the whole student portal is scoping-only | **35 of 41 controllers contain no policy call at all.** `RETENTION.md:31` claims guardian access is *"enforced by App\Policies\StudentPolicy, not just by query scoping"* — true for detail views, not for the list views. **P1-12** |
| IS-8.6 | Consistent domain calculations | **FAIL** | Admin (`AdminDashboardController.php:20`) and report (`ReportController.php:28`) count `whereIn('status',['present','late'])` as attended. Guardian (`GuardianDashboardController.php:34`) and student (`StudentDashboardController.php:30`) count `where('status','present')` only | **An admin and a guardian see different attendance percentages for the same child.** Surfaced by the §4.8 aggregate-function sweep. **P1-10** |
| IS-8.7 | Every model has a policy where it needs one | **PARTIAL** | 5 policies for 13 models. No policy: `Guardian`, `Coach`, `AgreementTemplate`, `AgreementSignature`, `GuardianStudent`, `SquadStudent`, `FrameworkPillar`, `FrameworkStrikeLevel` | Policies resolve by Laravel 11 auto-discovery; `AppServiceProvider` registers none explicitly (`app/Providers/AppServiceProvider.php:12-23` — both methods empty). **P1-13** |

### IS-9 — Child data protection

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-9.1 | Photos behind a policy-checked route | **PASS** (route) / **FAIL** (delivery) | Route `students.photo` carries `auth` + `signed` and `StudentPhotoController.php:14` calls `$this->authorize('viewPhoto', $student)`; base `Controller` does `use AuthorizesRequests` (`app/Http/Controllers/Controller.php:5-10`) so the call resolves. **But** all 7 call sites build the URL with `route()`, not `URL::signedRoute()` | Probe A: `hasValidSignature()` on a `route()`-generated URL = **false** → `ValidateSignature` aborts 403. **Every student photo in the app is broken.** **P0-1** |
| IS-9.2 | Failure is not silently masked | **FAIL** | All 7 `<img>` tags carry `onerror="this.style.display='none'"` — `coach/attendance-mark.blade.php:51`, `coach/squad-detail.blade.php:26`, `guardian/child-detail.blade.php:14`, `guardian/children.blade.php:9`, `guardian/dashboard.blade.php:9`, `student/dashboard.blade.php:4`, `student/profile.blade.php:5` | The 403 renders as a blank avatar. This is why the defect survived to delivery. Part of **P0-1** |
| IS-9.3 | No public storage URL for student media | **PASS** | Probe F — `Storage::url` / `asset('storage'` / `/storage/` across `app/` + `resources/views/` → **0 hits**; no `public/storage` symlink; `local` disk root is `storage/app/private` | Requirement genuinely met |
| IS-9.4 | Student portal exposes no teammate names/photos/rosters | **PASS** | `StudentScheduleController.php:19-33` queries `TrainingSession` with `coach.user` only, never students; `StudentAttendanceController.php:13-21` scoped to `$request->user()->student`; test `test_student_schedule_exposes_no_teammate_names` asserts `assertDontSee('Teammate Alpha')` over a real HTTP response | Real HTTP-level test, not a policy unit test |
| IS-9.5 | Drawn signatures on the private disk | **PASS** | `AgreementController.php:68-79` → `Storage::disk('local')->put('signatures/…')` | Rendered inline as base64 (see IS-8.3) rather than via a route |
| IS-9.6 | Sensitive views are audit-logged | **PASS** | `StudentController.php:85-88` logs `student.viewed`; `CoachSquadController.php:24-27` logs `squad.roster_viewed`; `activity_log` table present (`17-schema.log`) | Matches `RETENTION.md:29-30` |

### IS-11 / IS-12 — Framework reference, audit & retention

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-11.1 | Strike ladder fully table-driven | **PASS** | `framework_strike_levels` carries `level`, `triggers_timeout`, `timeout_minutes_min/max`, `triggers_parent_alert`, `triggers_meeting`, `triggers_suspension` (`17-schema.log`); trigger level read from `config('academy.suspension_trigger_level')` (`config/academy.php:24`); no hardcoded level count in `app/` or `resources/` | — |
| IS-11.2 | Framework content editable by admin, read-only publicly | **PASS** | `PillarController`/`StrikeLevelController` expose only `index`/`edit`/`update` under `role:admin` (`routes/web.php:139-145`); `PublicController::framework` is read-only | — |
| IS-11.3 | Pillars/levels bilingual | **PASS** | `17-schema.log` — all 6 content columns paired `_dv`/`_en`, all `NOT NULL` | Bilingual completeness enforced at the DB level |
| IS-12.1 | Activity logging configured | **PASS** | `spatie/laravel-activitylog` 4.12; `LogsActivity` on `User`, `Student`, `AgreementSignature`; `AuditController` + `admin/audit/index.blade.php` | — |
| IS-12.2 | Retention/purge path documented | **PASS** | `RETENTION.md:1-71` — storage inventory, per-role visibility, credential lifecycle, FK-ordered manual purge sequence | Purge is manual by design and says so |
| IS-12.3 | Retention doc matches reality | **FAIL** | `RETENTION.md:35` states photos *"are served exclusively through GET /students/{student}/photo, a signed, policy-checked route"* — the route exists but no caller signs its URL, so it serves nothing (P0-1). `RETENTION.md:31`'s policy claim is partial (IS-8.5) | Documentation describes intended, not actual, behaviour. **P1-26** |

### IS-13 — Test suite (detailed assessment in §5)

| Spec ref | Requirement | Verdict | Evidence | Notes |
|---|---|---|---|---|
| IS-13.1 | Named tests exist and pass | **PASS** | 43/43 passing, `05b-phpunit-testdox.log` | — |
| IS-13.2 | Tests genuinely exercise the rules | **PARTIAL** | 5 REAL-with-gaps, 5 WEAK, 4 MISSING areas — §5 | 2.6 assertions/test |
| IS-13.3 | Unit test coverage | **ABSENT** | `tests/Unit` contains 0 files (`09-file-inventory.log`); `phpunit.xml:8-10` declares the suite | Services with the densest rules have no unit tests |

---

## 4. TARGETED SWEEPS

### 4.1 Localisation completeness — **strongest area of the build**

**Command:** recursive PHP flatten-and-diff of all 32 lang files → `10-lang-key-diff.log`

| Metric | Count |
|---|---|
| `lang/dv` total keys | **570** |
| `lang/en` total keys | **570** |
| Present in EN, missing in DV | **0** |
| Present in DV, missing in EN | **0** |
| DV value identical to EN value | **2** — `common.locale.dv`, `common.locale.en` (correct: language endonyms) |
| DV values containing no Thaana | **3** — the 2 above plus placeholders |

**Blade text-node sweep — counts stated before classification, revisions retracted explicitly:**

- ~~**Pass 1: 487 hits**~~ — **RETRACTED.** Naive `<[^<>]*>` tag strip; did not handle multi-line tags. Almost entirely noise.
- ~~**Pass 2: 307 hits**~~ — **RETRACTED.** Length-preserving, but the same tag regex breaks on `>` inside attribute values (`?->`, `x-show="a > b"`), leaving attribute fragments that look like text.
- **Pass 3: 0 hits** — authoritative. Quote-aware character-level tokenizer; strips `{{ }}`, `{!! !!}`, `{{-- --}}`, `@php…@endphp`, `<script>`, `<style>`, balanced `@directive(...)`, and tags with quote-state tracking. **Zero untranslated Latin text nodes across ~90 views.** (`11-blade-hardcoded-text.log`)

Attribute sweep (`placeholder|title|alt|aria-label`): 42 hits, all `title="__('…')"` — false positives of my `[^"{$]` guard, since `__` starts with an underscore. **0 genuine hardcoded attribute strings.**

**`app/` hardcoded English** (`12-app-hardcoded-strings.log`): 51 user-facing message sites, **all** through `__()`. Multi-word Latin sentence literals in `app/`: **0**. No FormRequest defines `messages()` or `attributes()` — correctly delegated to `lang/{locale}/validation.php`, which supplies 25 translated `attributes` and 4 `custom` entries in **both** languages.

**`lang/dv/validation.php` is genuinely translated** (`13-validation-lang.log`) — not an English copy. Verified sample: `required`, `email`, `max` (all 4 sub-keys), `unique`, `confirmed`, `date`, `integer` all in Thaana.

**Bilingual column pairs** — all present and complete (IS-4.1). Direct `_dv`/`_en` access in views: **26 hits**, all legitimate (admin create/edit forms, which must edit both languages, plus the deliberate side-by-side `agreement-templates/show.blade.php`). `translated()` used **61×**. **No leakage of direct access into user-facing render paths.**

**Only defect:** `public.contact.address_value` and `public.contact.phone_value` = `[DV CONTENT PENDING]`.

### 4.2 RTL correctness

**Command:** `grep -rnoE` per utility across `resources/views resources/css tailwind.config.js` → `14-rtl-sweep.log`

**Raw hit count before classification: 19.** Per utility: `ml-` 0, `mr-` 0, `pl-` 0, `pr-` 0, `text-left` 0, `text-right` 0, `left-` 0, `right-` 0, `border-l` 2, `border-r` 3, `rounded-l` 14, `rounded-r` 0, `float-left` 0, `float-right` 0.

**Classified: 17 false positives, 2 genuine.**
- False: 14× `rounded-lg`, 3× `border-red-200` (prefix collisions).
- **Genuine: `resources/views/components/responsive-nav-link.blade.php:5` and `:6` — `border-l-4`.** Should be `border-s-4`. The active-nav indicator sits on the wrong edge in Dhivehi. **P2-17**

Logical utilities in use: `text-start` 105, `ms-` 7, `text-end` 4, `ps-` 2, `pe-` 2, `me-` 1, `start-` 1, `end-` 1. **The build genuinely used logical properties throughout** — 2 slips out of ~120 directional decisions.

**`dir` driven by locale in every layout — PASS.** All 4 files containing `<html`: `layouts/app.blade.php:38`, `layouts/guest.blade.php:2`, `layouts/public.blade.php:2`, and `guardian/agreement-download.blade.php:2` (which correctly uses `$signature->signed_locale->value`, not the active locale — the right call for a legal record).

**Thaana webfont — FAIL** (`15-font-check.log`):
- Bundled locally at `resources/fonts/MVBoli.ttf` (79,320 bytes), referenced by `resources/css/app.css:5-11`, emitted to `public/build/assets/MVBoli-DGgN0rby.ttf`. Not hotlinked — 0 external font references.
- **The font is MV Boli. You specified MV Faseyha.** **P1-9**
- MV Boli is a proprietary Microsoft font that ships with Windows; the file is byte-identical in size to the system font. Redistributing it as a webfont is a licensing exposure independent of the wrong-font issue.
- `tailwind.config.js:13` sets `fontFamily.sans = ['MV Boli','Faruma','system-ui','sans-serif']` — MV Boli is applied to **English** text too.
- Only a `.ttf` is shipped; no `.woff2`. ~79 KB uncompressed where woff2 would be ~30 KB.

### 4.3 Authentication and credentials

Covered in IS-3. Headline results:

- **Login by `users.username`, students by index number: PASS.**
- **Breeze register / forgot-password / reset-password / email-verification removed at all three layers — routes, controllers, views: PASS.** Checked all three, not just routes.
- **Password-leak sweep: PASS.** Generated passwords are returned from the service, handed to a view, and rendered in a single response body. No `Log::`, no `Mail::`, no session flash, no plaintext column, no re-display path. The only copy leaves via the admin's clipboard (`components/credential-issuer.blade.php:19-22`).
- **`must_change_password` gating: FAIL** — the agreement routes are outside it (P1-3).

### 4.4 Authorisation and child data protection

**Policies that exist (5):** `StudentPolicy`, `SquadPolicy`, `TrainingSessionPolicy`, `AttendancePolicy`, `UserPolicy`.
**Models with no policy (8):** `Guardian`, `Coach`, `AgreementTemplate`, `AgreementSignature`, `GuardianStudent`, `SquadStudent`, `FrameworkPillar`, `FrameworkStrikeLevel`.
**Controllers with zero `authorize`/`Gate`/`can` call: 35 of 41.**

Path traces to another student's data, one per role:

| Role | Path | Blocked by | Verdict |
|---|---|---|---|
| **Guardian** | `GET /portal/children/{student}` — another guardian's child | **Policy** — `GuardianStudentController.php:21` `$this->authorize('view',$student)` → `StudentPolicy::view` pivot check | **Blocked correctly** |
| **Guardian** | `GET /portal/attendance/{student}` | **Policy** — `GuardianAttendanceController.php:14` | **Blocked correctly** |
| **Guardian** | `GET /portal/children` (list) | **Query scoping only** — `GuardianStudentController.php:15-18` `$request->user()->guardian->students()` | Not exploitable (no ID input), but scoping-only, contra `RETENTION.md:31` |
| **Coach** | `GET /coach/squads/{squad}` — another coach's squad | **Policy** — `CoachSquadController.php:21` → `SquadPolicy::view` `head_coach_id` check | **Blocked correctly** |
| **Coach** | `POST /coach/sessions/{session}/attendance` | **Policy** — `AttendanceController.php:18,29` → `TrainingSessionPolicy::markAttendance` | **Blocked correctly** |
| **Student** | `GET /student/*` — any other student's data | **Query scoping only** — every student controller derives from `$request->user()->student`; no route accepts a student ID | Not exploitable; no policy involved |
| **Student** | `GET /students/{student}/photo` — arbitrary student ID | **Policy** — `StudentPhotoController.php:14` `viewPhoto` → `StudentPolicy::view` | Correct in principle; **route is 403 for everyone** (P0-1) |
| **Admin** | all | `role:admin` middleware only | Consistent with an admin-full-access model |

**Assessment:** every path that accepts an ID from the user and returns another person's data **is** Policy-blocked. The scoping-only paths take no ID and are not exploitable. Per the prompt's strict reading (§8.6: query scoping alone is a FAIL) IS-8.5 is **PARTIAL**, not PASS — but I found **no reachable cross-student data leak**.

**Student photos:** private disk ✓, policy-checked route ✓, no public URL ✓ (0 `Storage::url` hits) — **but delivery is 100% broken** (P0-1).
**Student portal teammate exposure:** clean, and covered by a real HTTP test.

### 4.5 The agreement gate

Verified by reading `app/Http/Middleware/EnsureAgreementSigned.php`, `bootstrap/app.php:34-39`, and the **resolved** middleware stacks from `route:list --json` — not by trusting filenames.

| Question | Answer |
|---|---|
| Applied to **both** guardian and student groups? | **Yes** — `routes/web.php:65` and `:80`, confirmed in Probe D |
| Does publishing a new version invalidate the gate? | **Yes** — `AgreementService.php:28-31` + `Student.php:92-104` |
| Is `template_snapshot` stored at signing time? | **Yes** — `AgreementService.php:101` |
| Is it **immutable thereafter**? | **No** — Probe C rewrote it, plus `signed_at` and `status`. **P1-4** |
| Does `signed_locale` match the snapshot language? | **Yes** — one `$locale` drives both (`:68,100-101`). Snapshot covers `body_*` only, not `title_*` or clause labels |
| Can a student sign their own agreement by any route? | **No** — `role:guardian` at route level plus a per-student `abort_unless` at `AgreementController.php:21,32` |

**Two bypasses found:**
1. **Fail-open** — `EnsureAgreementSigned.php:36-40`: if no template has `is_current = true`, the middleware calls `$next($request)` and every gated portal route opens. Unpublishing or deleting the current template silently disables the core Phase 1 rule. **P1-5**
2. **API bypass** — `/api/v1/children` and `/api/v1/schedule` return child data with no agreement middleware (Probe D). **P1-6**

Also noted: `guardian.profile.*` and `student.profile.*` are on the middleware's `ALLOWED_ROUTE_NAMES` (`:19-22`), so profile editing precedes signing. Plausibly intentional; unverifiable without the contract.

### 4.6 Business rules in the right layer

**Raw SQL / `selectRaw` / `whereRaw` / `DB::statement` / `DB::select`: 0 hits** across `app/`, `database/`, `resources/`. Clean.

**Business logic outside services — 6 occurrences:**

| # | Location | Logic |
|---|---|---|
| 1 | `AdminDashboardController.php:20` | Attendance % (`present`+`late`) |
| 2 | `ReportController.php:28-35` | Attendance % (`present`+`late`) |
| 3 | `GuardianDashboardController.php:34` | Attendance % (`present` only) — **inconsistent with 1–2** |
| 4 | `StudentDashboardController.php:30` | Attendance % (`present` only) — **inconsistent with 1–2** |
| 5 | `coach/attendance-mark.blade.php:3-9,45,69` | Default-status derivation from suspension state, in Blade |
| 6 | `guardian/agreement-download.blade.php:24-25` | `Storage::exists()` + `get()` + `base64_encode` in Blade |

**N+1 and missing eager loads** (`18-runtime-probes.log` Probe E; counts at demo-seed volume — see D5):

| Location | Pattern | Measured |
|---|---|---|
| `GuardianDashboardController.php:20-40` | Per-child `trainingSessions()->first()` + `attendances()->get()` inside `->map()` | 6 queries for 1 child |
| `Student.php:92-104` via `EnsureAgreementSigned.php:44-46` | Per-student `AgreementTemplate::current()->first()` + `exists()` — **on every guardian request** | 3 queries for 1 child (1 + 2N) |
| `Squad.php:65` `activeStudents()->count()` | Accessor querying per squad | 3 queries for 2 squads |
| `AttendancePolicy.php:13,36` | `$attendance->trainingSession->coach_id` — lazy load per authorised attendance | structural |

**Eager loading is present where it matters most** — `StudentController::index` uses `with(['activeEnrolment.squad','user'])` (`:23`), `show` uses a 4-relation `load()` (`:91`), `CoachSquadController`/`CoachDashboardController` use `withCount('activeStudents')`. The index and roster views are **not** the weak point; the per-request agreement accessor is.

### 4.7 Scope violations — **UNVERIFIABLE**

The out-of-scope list existed only in the missing brief (D1/D2). I cannot state that anything is out of scope. Inventory of the strongest candidate, for your decision:

**`/api/v1` — 6 routes, 4 controllers, 5 API Resources, `laravel/sanctum` dependency.** `README.md:154-158` claims the brief explicitly asked for it: *"laravel/sanctum was added for the /api/v1 bearer-token authentication the brief explicitly asks for ('API (prefix /api/v1, sanctum)' in the routes section)"*. If that quotation is accurate, the API is **in scope** — and it is then a P0 defect that it does not work at all (P0-2), not a scope violation. I cannot verify the quotation. **Either way it needs work: delete it, or make it function.**

Nothing else in the tree reads as speculative feature-building.

### 4.8 Enumeration discipline

Five independent sweeps, each reported separately. No single grep sees more than two of these classes.

| Sweep | Class | Raw count |
|---|---|---|
| **1** | String-referenced symbols | **1,098** — eager-load/relation strings 80, `route()` names 133, `view()` names 62, `config()` keys 8, `__()` keys 815 |
| **2** | Comparison operators on domain values | **29** — of which **12** compare against raw string literals rather than enum cases (`HasTranslatedAttributes.php:14,33`; `StudentController.php:35`; `AgreementService.php:101`; `admin/students/index.blade.php:22`; `coach/attendance-mark.blade.php:6,45,69`; `guardian/agreement-download.blade.php:2`; `layouts/{app,guest,public}.blade.php`) |
| **3** | Aggregate & math functions | **19** — including the 4 divergent attendance-% sites (P1-10) and `CredentialService.php:166` `max('id')+1` (P2-14). *This sweep is what surfaced both defects.* |
| **4** | Raw-SQL / `selectRaw` column literals | **0** |
| **5** | Assignment / compound-assignment on domain attributes | **1** direct (`StudentController.php:103` `->photo_path =`) + **104** array-key assignments inside `create()`/`update()`/`fill()` payloads |

**Why this matters for Stage 2:** every rename-or-signature change in the backlog has call sites in sweep 1 (~1,098 string references are invisible to a rename refactor) and sweep 5 (104 mass-assignment payload keys). Sweep 2's 12 raw-literal comparisons will not fail to compile if an enum value changes — they will silently evaluate false.

---

## 5. TEST SUITE ASSESSMENT

**Totals:** 43 tests, **43 passing**, 0 failing, 0 skipped, 0 incomplete, **112 assertions** (2.6 per test), 10 test classes, **0 unit tests**. Duration 20.82s. (`05-artisan-test.log`, `05b-phpunit-testdox.log`)

A 100% pass rate with 2.6 assertions per test and zero unit tests is a **finding, not a strength** — and it is demonstrated by the fact that the suite is fully green while two whole features are dead.

| Test | Class | Reasoning |
|---|---|---|
| `guardian_is_redirected_to_agreement_before_portal_access` | **REAL** | Real HTTP; asserts the exact target route |
| `student_is_blocked_until_guardian_signs` | **REAL** | Real HTTP; `assertForbidden` |
| `student_cannot_sign_own_agreement` | **WEAK** | Only `GET agreement.show`. Never exercises `POST agreement.sign` — the action the name claims to cover |
| `new_template_version_forces_resign` | **REAL** | Genuine publish→invalidate cycle; asserts the model accessor, not the HTTP gate |
| `signature_stores_immutable_template_snapshot` | **WEAK** | Proves only that mutating the *template* leaves the snapshot alone. Probe C shows the snapshot itself **is** mutable — the test would pass against the broken implementation. Name over-claims |
| `snapshot_matches_the_locale_the_guardian_signed_in` | **REAL** | Asserts both `signed_locale` and snapshot body. Only covers `dv` |
| `publish_blocked_when_either_language_incomplete` | **REAL** | Exercises the real service rule |
| `publish_succeeds_when_both_languages_complete` | **REAL** | — |
| `only_assigned_coach_or_admin_can_mark_attendance` | **REAL** | — |
| `suspended_student_cannot_be_marked_present` | **REAL** | — |
| `marking_attendance_twice_updates_not_duplicates` | **REAL** | Genuine idempotence check; would not catch the missing DB unique key under concurrency |
| `login_screen_can_be_rendered` | **WEAK** | Smoke test |
| `student_can_log_in_with_index_number` | **REAL** | — |
| `guardian_can_log_in_with_phone_number` | **REAL** | — |
| `index_number_lookup_is_case_and_space_insensitive` | **REAL** | — |
| `inactive_user_cannot_log_in` | **REAL** | — |
| `first_login_forces_password_change` | **REAL** | — |
| `no_public_registration_route_exists` | **REAL** | Asserts route absence directly |
| `users_can_logout` | **REAL** | — |
| `guardian_cannot_view_another_guardians_child` | **WEAK** | `$user->can('view',$student)` — tests the **Policy in isolation**, never the HTTP route. Proves nothing about whether any controller calls it |
| `coach_cannot_view_student_outside_own_squad` | **WEAK** | Same — `->can()` only |
| `student_cannot_view_another_students_record` | **WEAK** | Same — `->can()` only |
| `student_schedule_exposes_no_teammate_names` | **REAL** | Real HTTP + `assertDontSee` on seeded teammate names. The best test in the suite |
| `issuing_login_sets_username_to_index_number` | **REAL** | — |
| `reissuing_resets_password_without_creating_second_account` | **REAL** | — |
| `changing_index_number_updates_linked_username` | **REAL** | — |
| `generated_password_is_not_persisted_in_plain_text` | **REAL** | — |
| `new_enrolment_closes_previous_active_enrolment` | **REAL** | — |
| `enrolment_blocked_when_squad_at_capacity` | **REAL** | — |
| `dhivehi_locale_renders_rtl_direction` | **REAL** | — |
| `english_locale_renders_ltr_direction` | **REAL** | — |
| `switching_locale_persists_for_authenticated_user` | **REAL** | — |
| `invalid_locale_is_rejected` | **REAL** | — |
| `lang_dv_and_en_have_identical_key_sets` | **REAL** | Genuine recursive diff — I reproduced its result independently (570/570) |
| `no_untranslated_literals_in_blade_views` | **WEAK** | Regex `/>\s*([A-Z][a-zA-Z]{2,}(?:\s+[a-zA-Z]{2,}){1,})\s*</` requires a capital letter **and** ≥2 words **and** direct adjacency to tags; uses `preg_match` (first hit per file only); its tag-stripper has the same `>`-in-attribute bug my pass 2 had. Would miss every single-word literal ("Save", "Cancel") |
| `generate_skips_dates_with_existing_sessions` | **REAL** | — |
| `overlapping_session_is_rejected` | **REAL** | — |
| `admin_can_create_student_with_primary_guardian` | **REAL** | — |
| `student_creation_requires_at_least_one_guardian` | **REAL** | — |
| `duplicate_index_number_is_rejected` | **REAL** | — |
| `age_outside_5_to_18_is_rejected` | **REAL** | — |
| `thaana_input_is_accepted_and_stored_intact` | **REAL** | — |
| `non_admin_cannot_create_a_student` | **REAL** | — |

**Tally: 36 REAL, 7 WEAK, 0 outright shells.**

**MISSING — no test exists for any of these, which is exactly why the P0s shipped:**

1. **Student photo route reachability.** No test requests `students.photo` at all. A single `$this->actingAs($admin)->get(route('students.photo',$s))->assertOk()` would have caught P0-1 on day one.
2. **Any `/api/v1` endpoint.** Zero API tests. `POST /api/v1/auth/login` 500s and nothing notices.
3. **`must_change_password` coverage of the agreement routes.** `first_login_forces_password_change` tests the portal, never `agreement.show`.
4. **Agreement-gate fail-open with no current template.**
5. **Attendance-percentage consistency across roles.**
6. **Signature-record immutability** (as opposed to template decoupling).
7. **Any unit test** for the 6 services — 787 lines of the densest business logic in the codebase, covered only indirectly.

---

## 6. VERDICT: **REPAIR**

Defended with counts from the findings above.

### Why not rebuild

**The schema is right.** This is decisive, because every layer above inherits it. `17-schema.log` shows 13 correctly-normalised domain tables, complete `*_dv`/`*_en` pairing on all 6 bilingual content entities, appropriate enums, FKs and status indexes, soft deletes where retention requires them, and a signature table that already carries `template_snapshot`, `signed_locale`, `ip_address`, `user_agent` and a revocation path. The four missing uniqueness constraints (IS-4.3) and the one missing table (`personal_access_tokens`) are **additive migrations** — no data reshaping, no rewrite of anything above.

**The layering is right.** 6 services hold the business rules; all 11 domain-rule exceptions live in `app/Services/`; **0 raw SQL** anywhere. The 6 layering violations are localised and individually small.

**Localisation — the hardest thing to retrofit — is essentially done.** 570/570 key parity, a genuinely translated `validation.php` with 25 translated attribute names, **0** untranslated Blade text nodes under a strict quote-aware sweep, 61 uses of the translation trait with no leakage of direct `_dv`/`_en` access into render paths, and 105 `text-start` vs 2 physical-direction slips. Rebuilding discards roughly 1,100 correct translation keys and ~120 correct directional decisions to fix 4 defects.

**The defects are enumerable and localised.** 26 backlog items. **The two P0s are four-line fixes**: add `HasApiTokens` + one migration; change `route(` to `URL::signedRoute(` at 7 call sites.

### What is genuinely wrong

The failure is not architectural — it is **verification**. Two entire features were shipped without a single test touching them, and one of them (photos) actively hides its own failure with `onerror`. Every P0 and most P1s trace to that same gap, not to bad structure.

### Decisive factors

| Factor | Reading |
|---|---|
| Schema correctness | Sound — **strongest argument against rebuild** |
| Service layer | Sound |
| Localisation | Near-complete; expensive to recreate |
| P0 count | 2, both trivially fixable |
| Test integrity | Weak — the real problem |
| Missing SPEC.md | **Blocks acceptance regardless of code quality** |

### Effort estimates

| Option | Effort | Note |
|---|---|---|
| **REPAIR (recommended)** | **3–5 days** — ~0.5d P0, ~2d P1, ~1.5d P2, ~1d test hardening | Backlog in §7 is executable as-is |
| Partial rebuild | 2–3 weeks | Would discard working layers; nothing justifies it |
| Full rebuild | 6–8 weeks | Not defensible — the schema and service layer are sound |

### The real blocker

**Recover `SPEC.md` before accepting anything.** Repair is the right call on code quality, but acceptance cannot be judged against a contract nobody has. Re-run §3 against the real spec once recovered — it may add requirements this audit could not see, particularly around IS-10 (out of scope).

---

## 7. REMEDIATION BACKLOG

Ordered by severity, then dependency. Executable without re-reading the codebase.

### P0 — app broken / security

| ID | Title | Spec | Files | Proving test | Depends on |
|---|---|---|---|---|---|
| **P0-1** | Student photos 403 on every request — views build unsigned URLs for a `signed` route | IS-9.1, IS-9.2 | `resources/views/coach/attendance-mark.blade.php:51`, `coach/squad-detail.blade.php:26`, `guardian/child-detail.blade.php:14`, `guardian/children.blade.php:9`, `guardian/dashboard.blade.php:9`, `student/dashboard.blade.php:4`, `student/profile.blade.php:5` — replace `route(` with `URL::signedRoute(`; **also drop `onerror="this.style.display='none'"`** so future breakage is visible | `StudentPhotoTest::test_photo_route_is_reachable_with_a_generated_url`, `::test_unauthorised_user_cannot_view_another_students_photo` | — |
| **P0-2** | Entire `/api/v1` 500s — `User` lacks `HasApiTokens`, no `personal_access_tokens` table | IS-4.4, IS-3.10 | `app/Models/User.php:19`, new migration | `Api/AuthTest::test_api_login_returns_a_bearer_token` | Decide P1-6 first — if the API is out of scope, **delete** rather than fix |

### P1 — spec violation blocking acceptance

| ID | Title | Spec | Files | Proving test | Depends on |
|---|---|---|---|---|---|
| **P1-0** | **Recover `SPEC.md`; re-run §3 against it** | D1 | repo root | `n/a — process gate` | Blocks final acceptance of every item |
| **P1-3** | Guardian can sign the binding agreement while still on the admin-issued password | IS-3.9 | `routes/web.php:57-61` — add `password.changed` | `AgreementGateTest::test_guardian_must_change_password_before_signing` | — |
| **P1-4** | `template_snapshot`, `signed_at`, `status` on a signed agreement are freely rewritable | IS-7.4 | `app/Models/AgreementSignature.php:19-34` — remove from `$fillable`, add an `updating` guard | `AgreementGateTest::test_signed_snapshot_cannot_be_modified_after_signing` | — |
| **P1-5** | Agreement gate **fails open** when no template is current | IS-7.7 | `app/Http/Middleware/EnsureAgreementSigned.php:36-40` — deny, don't pass through | `AgreementGateTest::test_gate_denies_when_no_template_is_published` | — |
| **P1-6** | API bypasses the agreement gate and `must_change_password` | IS-7.7, IS-3.10 | `routes/api.php:11-17` | `Api/AgreementGateTest::test_api_children_blocked_until_agreement_signed` | P0-2 |
| **P1-7** | API login has no rate limiting (web has a 5-attempt throttle) | IS-3.10 | `app/Http/Controllers/Api/V1/AuthController.php:14-40` | `Api/AuthTest::test_api_login_is_rate_limited` | P0-2 |
| **P1-8** | API login ignores `must_change_password` | IS-3.10 | `AuthController.php:32-38` | `Api/AuthTest::test_api_login_reports_forced_password_change` | P0-2 |
| **P1-9** | Wrong Thaana font — **MV Boli bundled, MV Faseyha required**; also a proprietary-font redistribution exposure, and applied to Latin text | IS-5.x | `resources/fonts/MVBoli.ttf` (remove), `resources/css/app.css:5-16`, `tailwind.config.js:13` — ship MV Faseyha as `.woff2`, scope it to `[dir="rtl"]`/`.font-thaana`, give Latin its own stack | `LocalisationTest::test_thaana_webfont_is_bundled_locally` | — |
| **P1-10** | Attendance % inconsistent across roles — admin/report count `late` as attended, guardian/student do not | IS-8.6 | `AdminDashboardController.php:20`, `ReportController.php:28-35`, `GuardianDashboardController.php:34`, `StudentDashboardController.php:30` — extract to `AttendanceService::attendancePercentage()` | `AttendanceTest::test_attendance_percentage_is_identical_for_admin_and_guardian` | — |
| **P1-11** | `[DV CONTENT PENDING]` placeholders ship to the public contact page | IS-5.5 | `lang/dv/public.php` | `LocalisationTest::test_no_placeholder_content_in_dv_lang_files` | — |
| **P1-12** | Guardian list views are scoping-only; `RETENTION.md:31` claims Policy enforcement | IS-8.5 | `GuardianStudentController.php:15-18`, `GuardianDashboardController.php:14` — add `viewAny`/`Gate` | `AuthorizationTest::test_guardian_children_index_is_policy_scoped` | — |
| **P1-13** | 8 of 13 models have no policy; `AppServiceProvider` registers none | IS-8.7 | `app/Policies/` (+`GuardianPolicy`, `CoachPolicy`, `AgreementTemplatePolicy`, `AgreementSignaturePolicy`), `app/Providers/AppServiceProvider.php:19-23` | `AuthorizationTest::test_every_domain_model_has_a_registered_policy` | — |
| **P1-26** | `RETENTION.md` documents behaviour that does not work | IS-12.3 | `RETENTION.md:31,35` | `n/a — doc review after P0-1, P1-12` | P0-1, P1-12 |

### P2 — quality, standards, performance

| ID | Title | Spec | Files | Proving test | Depends on |
|---|---|---|---|---|---|
| **P2-14** | `coach_no` generated as `max('id')+1` — collides under concurrency and after deletes | IS-6.8 | `app/Services/CredentialService.php:164-169` | `CredentialTest::test_concurrent_coach_creation_yields_unique_coach_numbers` | — |
| **P2-15** | N+1: guardian dashboard (per-child session+attendance queries), `has_signed_current_agreement` (1+2N **on every guardian request**), `Squad::activeStudentCount` | IS-8.2 | `GuardianDashboardController.php:20-40`, `Student.php:92-104`, `EnsureAgreementSigned.php:44-46`, `Squad.php:65` | `PerformanceTest::test_guardian_dashboard_query_count_is_constant_in_child_count` | — |
| **P2-16** | Missing DB uniqueness on `attendances`, `guardian_student`, `agreement_signatures`, `squad_student` active enrolment | IS-4.3 | new migration | `AttendanceTest::test_duplicate_attendance_row_is_rejected_by_the_database` | — |
| **P2-17** | `border-l-4` (physical) in the responsive nav — active indicator on the wrong edge in RTL | §4.2 | `resources/views/components/responsive-nav-link.blade.php:5,6` → `border-s-4` | `LocalisationTest::test_no_physical_direction_utilities_in_views` | — |
| **P2-18** | Disk I/O in a Blade view | IS-8.3 | `resources/views/guardian/agreement-download.blade.php:24-25` | `AgreementTemplateTest::test_signature_image_is_resolved_before_render` | — |
| **P2-19** | Business logic + raw enum-string comparisons in Blade | IS-8.3, sweep 2 | `resources/views/coach/attendance-mark.blade.php:3-9,45,69` | `AttendanceTest::test_default_status_for_suspended_student_is_set_by_the_service` | — |
| **P2-20** | Zero unit tests; 2.6 assertions/test | IS-13.3 | `tests/Unit/` — cover the 6 services | `Unit/AgreementServiceTest`, `Unit/EnrolmentServiceTest`, `Unit/SessionSchedulingServiceTest`, `Unit/AttendanceServiceTest`, `Unit/CredentialServiceTest`, `Unit/StudentRegistrationServiceTest` | — |
| **P2-21** | Credential resets not transactional (unlike issue/create) | IS-6.6 | `CredentialService.php:111-122,151-162` | `CredentialTest::test_credential_reset_is_atomic` | — |
| **P2-22** | `test_no_untranslated_literals_in_blade_views` is a weak heuristic (first-match-only, ≥2 words, capital-initial, `>`-in-attribute bug) | IS-13.2 | `tests/Feature/LocalisationTest.php:92-124` — port the quote-aware tokenizer from `11-blade-hardcoded-text.log` | `LocalisationTest::test_no_untranslated_literals_in_blade_views` (strengthened) | — |
| **P2-23** | `student_cannot_sign_own_agreement` never exercises `POST agreement.sign` | IS-13.2 | `tests/Feature/AgreementGateTest.php:51-63` | same test, extended to POST | — |
| **P2-24** | `AuthorizationTest` uses `->can()` only — never proves any HTTP route is blocked | IS-13.2 | `tests/Feature/AuthorizationTest.php:18-54` — convert to HTTP requests asserting 403 | `AuthorizationTest::test_guardian_cannot_reach_another_guardians_child_over_http` | — |
| **P2-25** | Snapshot stores `body_*` only — not `title_*`, not the consent-clause labels agreed to | IS-7.5 | `app/Services/AgreementService.php:101` | `AgreementGateTest::test_snapshot_captures_title_body_and_clause_labels` | P1-4 |

### P3 — cosmetic

| ID | Title | Spec | Files | Proving test | Depends on |
|---|---|---|---|---|---|
| **P3-27** | Font shipped as 79 KB `.ttf`; no `.woff2` | §4.2 | `resources/fonts/`, `resources/css/app.css:7` | `n/a — build-size check` | P1-9 |
| **P3-28** | `is_active` checked after `Auth::attempt` succeeds, briefly establishing a session | IS-3.8 | `app/Http/Requests/Auth/LoginRequest.php:40-54` | `AuthenticationTest::test_inactive_user_never_establishes_a_session` | — |

---

## 8. DEFINITION OF DONE — PROOF OF ZERO APPLICATION CHANGES

```
$ git status --short
?? AUDIT-EVIDENCE/

$ git diff --stat HEAD
(no output — no tracked file modified)

$ git status --porcelain --untracked-files=all
?? AUDIT-EVIDENCE/01-git.log
?? AUDIT-EVIDENCE/02-composer-install.log
?? AUDIT-EVIDENCE/03-npm-install-build.log
?? AUDIT-EVIDENCE/04-migrate-fresh-seed.log
?? AUDIT-EVIDENCE/05-artisan-test.log
?? AUDIT-EVIDENCE/05b-phpunit-testdox.log
?? AUDIT-EVIDENCE/06-route-list.json
?? AUDIT-EVIDENCE/06-route-list.txt
?? AUDIT-EVIDENCE/07-pint-test.log
?? AUDIT-EVIDENCE/08-artisan-about.log
?? AUDIT-EVIDENCE/09-file-inventory.log
?? AUDIT-EVIDENCE/10-lang-key-diff.log
?? AUDIT-EVIDENCE/11-blade-hardcoded-text.log
?? AUDIT-EVIDENCE/12-app-hardcoded-strings.log
?? AUDIT-EVIDENCE/13-validation-lang.log
?? AUDIT-EVIDENCE/14-rtl-sweep.log
?? AUDIT-EVIDENCE/15-font-check.log
?? AUDIT-EVIDENCE/16-layouts-dir.log
?? AUDIT-EVIDENCE/17-schema.log
?? AUDIT-EVIDENCE/18-runtime-probes.log
```

**Zero application files created, modified or deleted.** `AUDIT.md` and `AUDIT-EVIDENCE/` are the only additions, exactly as the prompt's boundary permits. `composer install` and `npm install` wrote to the gitignored `vendor/` and `node_modules/`; `npm run build` wrote to the gitignored `public/build/`. The database work targeted the scratch database `wacademy_audit_scratch`; the development database `wacademy` was never dropped or modified. The one probe row created in the scratch DB (Probe C) was force-deleted in the same command.

**Budget:** every section of the prompt was completed. §4.7 is answered UNVERIFIABLE for the substantive reason given in D2, not for lack of budget.

**STOP — no remediation performed. Awaiting instruction.**
