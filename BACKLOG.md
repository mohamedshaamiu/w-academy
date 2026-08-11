# BACKLOG — W-ACADEMY PHASE 1 REMEDIATION

**Supersedes AUDIT.md §7 in full.** Keyed to `SPEC.md`, not to the inferred spec.
**Branch:** `remediation/phase-1` (from `46a6336`) · **Produced:** WP-0

**Composition:** 26 AUDIT.md items − 3 withdrawn (see `DELTA.md` §3) + 13 new from SPEC.md requirements never assessed = **36 items**.

Severities: **P0** security / child-data exposure / data loss / feature dead · **P1** spec violation blocking acceptance · **P2** quality, standards, performance · **P3** cosmetic.

Ordered by severity, then dependency. Items are executable without re-reading the codebase.

---

## P0 — BLOCKING

| ID | Severity | Title | SPEC ref | Files affected | Proving test | Depends on |
|---|---|---|---|---|---|---|
| **P0-1** | P0 | Student photos return 403 on every render — views build unsigned URLs for a `signed` route, and `onerror` hides the failure | §8.6 | `resources/views/coach/attendance-mark.blade.php:51`, `coach/squad-detail.blade.php:26`, `guardian/child-detail.blade.php:14`, `guardian/children.blade.php:9`, `guardian/dashboard.blade.php:9`, `student/dashboard.blade.php:4`, `student/profile.blade.php:5`; new shared component `resources/views/components/student-photo.blade.php` | `StudentPhotoTest::test_signed_photo_url_renders_for_authorised_viewer`, `::test_unsigned_photo_url_is_rejected`, `::test_guardian_cannot_load_another_childs_photo`, `::test_photo_route_is_not_publicly_reachable` | WP-1 |
| **P0-2** | P0 | All six `/api/v1` endpoints 500 — `User` lacks `HasApiTokens`, no `personal_access_tokens` table | §5, §7 | `app/Models/User.php:19`; new migration | 12 × per-endpoint happy-path + wrong-role; `ApiLocaleTest::test_accept_language_switches_resource_language` | WP-1 |
| **P0-3** | P0 | Signed agreements are mutable — 5 immutable fields rewritable; `AgreementService::revoke()` does not exist | §8.3 | `app/Models/AgreementSignature.php:19-45`, `app/Services/AgreementService.php` | `AgreementSignatureTest::test_signed_agreement_fields_cannot_be_updated`, `::test_revocation_is_the_only_mutation_path`, `::test_revoked_signature_no_longer_satisfies_the_gate` | WP-1 |
| **P0-4** | P0 | Agreement gate **fails open** when no template is current — all portals unlock | §8.3 | `app/Http/Middleware/EnsureAgreementSigned.php:36-40` | `AgreementGateTest::test_gate_denies_when_no_current_template_exists`, `::test_admin_retains_access_when_no_current_template_exists` | WP-1 |
| **P0-5** | P0 | Binding agreement can be signed while on the admin-issued password | §7, §8.3 | `routes/web.php:57-61` | `AgreementGateTest::test_guardian_must_change_password_before_signing` | WP-1 |

## P1 — SPEC VIOLATION, BLOCKS ACCEPTANCE

| ID | Severity | Title | SPEC ref | Files affected | Proving test | Depends on |
|---|---|---|---|---|---|---|
| **P1-A** | P1 | Attendance % diverges by role; `AttendanceStatisticsService` and `config('academy.attendance')` do not exist | §8.7, §11 | new `app/Services/AttendanceStatisticsService.php`; `config/academy.php`; `Admin/AdminDashboardController.php:20`, `Admin/ReportController.php:28-35`, `Guardian/GuardianDashboardController.php:34`, `Student/StudentDashboardController.php:30` | `AttendanceStatisticsTest::test_all_roles_see_identical_percentage_for_same_student`, `::test_late_treatment_follows_config`, `::test_excused_is_excluded_from_denominator`, `::test_no_percentage_is_computed_outside_the_service` | P0-2 (API resource must call it) |
| **P1-B** | P1 | Proprietary MV Boli bundled and set as global `sans`; no `FONT-LICENCE.md` | §3.4, §14 | `resources/fonts/MVBoli.ttf` (remove), `resources/css/app.css:5-20`, `tailwind.config.js:13`, new `FONT-LICENCE.md` | `LocalisationTest::test_thaana_font_class_applies_only_in_dv_locale`, `::test_no_proprietary_font_binary_in_repository` | — |
| **P1-C** | P1 | Guardian children **index** and dashboard are query-scoped only — §8.6 names policy enforcement as mandatory | §8.6 | `Guardian/GuardianStudentController.php:15-18`, `Guardian/GuardianDashboardController.php:14`; `app/Policies/StudentPolicy.php` (`viewAny`) | `AuthorizationTest::test_guardian_cannot_view_another_guardians_child` (HTTP form) | WP-1 |
| **P1-D** | P1 | Three `AuthorizationTest` cases assert via `->can()` only — §13 forbids this explicitly | §13 | `tests/Feature/AuthorizationTest.php:18-54` | same three names, converted to HTTP + retained policy assertion | — |
| **P1-E** | P1 | `RouteCoverageTest` absent — no systematic proof that routes reject guests and wrong roles | §13, §4-DoD | new `tests/Feature/RouteCoverageTest.php` | `RouteCoverageTest::test_every_non_public_route_rejects_guests_and_wrong_roles` | — |
| **P1-F** | P1 | `/api/v1` bypasses the agreement gate and `must_change_password` | §8.3 | `routes/api.php:11-17` | `Api/AgreementGateTest::test_api_children_blocked_until_agreement_signed` | P0-2 |
| **P1-G** | P1 | API login has no throttle (web has 5/min) | §8.1 | `app/Http/Controllers/Api/V1/AuthController.php:14-40` | `Api/AuthTest::test_api_login_is_rate_limited` | P0-2 |

## P2 — QUALITY, STANDARDS, PERFORMANCE

| ID | Severity | Title | SPEC ref | Files affected | Proving test | Depends on |
|---|---|---|---|---|---|---|
| **P2-01** | P2 | `coach_no` generated as `max('id')+1` — collides under concurrency and after deletes | §8.2 | `app/Services/CredentialService.php:164-169` | `CredentialTest::test_concurrent_coach_creation_yields_unique_coach_numbers` | — |
| **P2-02** | P2 | N+1: guardian dashboard (per-child queries), `hasSignedCurrentAgreement` (1+2N on **every** guardian request), `Squad::activeStudentCount` | §12 | `Guardian/GuardianDashboardController.php:20-40`, `app/Models/Student.php:92-104`, `app/Http/Middleware/EnsureAgreementSigned.php:44-46`, `app/Models/Squad.php:65` | `PerformanceTest::test_guardian_dashboard_query_count_is_constant_in_child_count` | — |
| **P2-03** | P2 | No query-count assertions anywhere — §12 requires eager loading be asserted in tests | §12 | `tests/Feature/PerformanceTest.php` (new) | `PerformanceTest::test_index_and_roster_views_do_not_n_plus_one` | P2-02 |
| **P2-04** | P2 | Fallback hint rendered at only 2 of ~61 `translated()` sites | §3.5 | `app/Concerns/HasTranslatedAttributes.php`; new `resources/views/components/translated-value.blade.php`; all `translated()` render sites | `LocalisationTest::test_fallback_render_shows_translated_hint` | — |
| **P2-05** | P2 | `venue_dv`/`venue_en` savable one without the other — §3.5 requires both-or-neither | §3.5 | `app/Http/Requests/Admin/StoreSquadRequest.php:21-22`, `UpdateSquadRequest`, `StoreSessionRequest.php:21-22` | `SquadTest::test_venue_requires_both_languages_or_neither` | — |
| **P2-06** | P2 | Linking to an existing guardian phone surfaces no translated notice | §8.2 | `Admin/GuardianController.php:36-41`, `lang/{dv,en}/admin.php` | `GuardianTest::test_linking_existing_phone_surfaces_translated_notice` | — |
| **P2-07** | P2 | Business logic + raw enum-string comparisons in Blade (suspended → forced `excused`) | §8, §8.5, §12 | `resources/views/coach/attendance-mark.blade.php:3-9,45,69` | `AttendanceTest::test_suspended_roster_status_is_locked_by_the_service` | — |
| **P2-08** | P2 | Disk I/O in a Blade view (`Storage::exists` + `get` + `base64_encode`) | §8, §12 | `resources/views/guardian/agreement-download.blade.php:24-25` | `AgreementTest::test_signature_image_is_resolved_before_render` | — |
| **P2-09** | P2 | Zero unit tests; 2.6 assertions/test across 43 tests | §11, §13 | `tests/Unit/` — one per service | `Unit/{AgreementService,AttendanceStatistics,Enrolment,SessionScheduling,Attendance,Credential,StudentRegistration}Test` | P1-A |
| **P2-10** | P2 | `LocalisationTest::test_no_untranslated_literals_in_blade_views` is a weak heuristic (first-match-only, ≥2 words, capital-initial, `>`-in-attribute bug) | §13 | `tests/Feature/LocalisationTest.php:92-124` | same name, strengthened with the quote-aware tokenizer from `AUDIT-EVIDENCE/11-blade-hardcoded-text.log` | — |
| **P2-11** | P2 | `AgreementGateTest::test_student_cannot_sign_own_agreement` never exercises `POST /agreement/{student}` | §13 | `tests/Feature/AgreementGateTest.php:51-63` | same name, extended to POST | — |
| **P2-12** | P2 | `AgreementGateTest::test_signature_stores_immutable_template_snapshot` proves only template decoupling, not immutability | §13, §8.3 | `tests/Feature/AgreementGateTest.php:79-90` | same name, plus `AgreementSignatureTest` (P0-3) | P0-3 |
| **P2-13** | P2 | Credential resets not transactional (unlike issue/create) | §8.2 | `app/Services/CredentialService.php:111-122,151-162` | `CredentialTest::test_credential_reset_is_atomic` | — |
| **P2-14** | P2 | `template_snapshot` stores `body_*` only — not `title_*`, not the clause labels actually agreed to | §8.3 | `app/Services/AgreementService.php:101` | `AgreementSignatureTest::test_snapshot_captures_title_body_and_clause_labels` | P0-3 |
| **P2-15** | P2 | `responsive-nav-link` uses physical `border-l-4` — active indicator on the wrong edge in RTL | §3.4, §12 | `resources/views/components/responsive-nav-link.blade.php:5,6` | `LocalisationTest::test_no_physical_direction_utilities_in_views` | — |
| **P2-16** | P2 | Directional affordances (icons, chevrons, progress bars) unverified under RTL | §3.4 | `resources/views/**` — audit pass required | `LocalisationTest::test_no_physical_direction_utilities_in_views` (extended) | P2-15 |
| **P2-17** | P2 | 29 §9 UI screens never audited for specified content | §9 | `resources/views/**` | per-screen assertions folded into existing feature tests | WP-1 |
| **P2-18** | P2 | `RETENTION.md` documents behaviour that does not work (photo delivery; policy enforcement on lists) | §8.6 | `RETENTION.md:31,35` | doc review after P0-1, P1-C | P0-1, P1-C |

## P3 — COSMETIC

| ID | Severity | Title | SPEC ref | Files affected | Proving test | Depends on |
|---|---|---|---|---|---|---|
| **P3-01** | P3 | No disk named `private`; code uses `local` (root `storage/app/private`) | §8.6, §12 | `config/filesystems.php:31-39`, `.env.example`, all `Storage::disk('local')` call sites | `StudentPhotoTest::test_photos_are_stored_on_the_private_disk` | P0-1 |
| **P3-02** | P3 | `index_number` form regex accepts lowercase; contract specifies `/^[A-Z0-9\-]{3,20}$/` | §4 | `app/Http/Requests/Admin/StoreStudentRequest.php:22`, `UpdateStudentRequest` | `StudentCreationTest::test_index_number_rejects_lowercase_input` | — |
| **P3-03** | P3 | Login throttle keys on `username\|ip`; contract says per username per minute | §8.1 | `app/Http/Requests/Auth/LoginRequest.php:76-79` | `AuthenticationTest::test_throttle_is_keyed_per_username` | — |
| **P3-04** | P3 | Commented-out code in the published spatie migration stub | §12 | `database/migrations/2026_08_11_084916_create_permission_tables.php:24,34` | `n/a — pint/grep gate` | — |
| **P3-05** | P3 | Font shipped as 79 KB `.ttf`; no `.woff2` | §3.4 | `resources/fonts/`, `resources/css/app.css` | `n/a — build-size check` | P1-B |
| **P3-06** | P3 | `is_active` checked after `Auth::attempt` succeeds, briefly establishing a session | §8.1 | `app/Http/Requests/Auth/LoginRequest.php:40-54` | `AuthenticationTest::test_inactive_user_never_establishes_a_session` | — |

---

## WITHDRAWN FROM AUDIT.md §7 — DO NOT ACTION

| Former ID | Title | Why withdrawn |
|---|---|---|
| ~~P1-11~~ | `[DV CONTENT PENDING]` placeholders | **Required** by SPEC.md §3.6/§12/§14. Build was correct. See `DELTA.md` §3 Retraction 3. |
| ~~P1-13~~ | "8 of 13 models have no policy" | SPEC.md §11 specifies exactly 5 policies; all 5 exist. See Retraction 2. |
| ~~P2-16~~ | "Missing DB uniqueness on 4 tables" | 3 constraints exist and are live; the 4th (`squad_student`) is **explicitly forbidden** by SPEC.md §4. See Retraction 1. |

---

## DECISIONS REQUIRED FROM YOU (blocking their items)

| # | Decision | Blocks | Default if unanswered |
|---|---|---|---|
| **D-1** | Does `late` count as attended? Does `excused` leave the denominator? SPEC.md §14 lists this as an open academy policy decision. | P1-A | I will implement `late = attended`, `excused = excluded from denominator`, config-driven — and flag it as **unconfirmed**, not approved |
| **D-2** | Which licensed Thaana webfont? SPEC.md §3.4 requires a licence permitting web redistribution; §14 defers procurement to before go-live. Your Stage 1 note said **MV Faseyha**, but SPEC.md names no face. Per WP-3 I will not choose one. | P1-B closure | Remove MV Boli, wire `--font-thaana` to a documented fallback stack, ship `FONT-LICENCE.md` — go-live stays **blocked** on procurement |
| **D-3** | `index_number` origin — academy-issued or school exam index? SPEC.md §14 flags that the latter repeats across schools and changes yearly, which would break the unique-username design. | Phase 2 schema | No action in Phase 1; recorded as a live risk |

---

## SEQUENCING

WP-1 (make the suite load-bearing) precedes all repair, as instructed: 30 of SPEC.md §13's 58 named tests do not exist, and every P0 sits inside that gap.

```
WP-1  P1-D, P1-E  + write the 30 missing §13 tests as failing
WP-2  P0-1 → P0-2 → P0-3 → P0-4 → P0-5     (fixed order)
WP-3  P1-A, P1-B                            (needs D-1, D-2)
WP-4+ proposed after WP-3 approval
```
