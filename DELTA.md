# DELTA — AUDIT.md INFERRED-SPEC vs. SPEC.md

**Branch:** `remediation/phase-1` (from `46a6336`)
**SPEC.md received:** 47,018 bytes, 893 lines
**Purpose:** WP-0 item 2. Only rows whose verdict **changes** appear here. Rows where the inferred verdict already matched SPEC.md are omitted by design — see AUDIT.md §3 for those.

**Headline:** 14 verdicts change. **9 improve** (the build is better than the audit said), **3 are unchanged in verdict but escalate in severity**, and **2 of my Stage 1 findings were outright wrong and are retracted with the method error that caused them.**

---

## 1. VERDICTS THAT CHANGED

| Spec ref | Requirement | Inferred verdict | True verdict | Why it changed |
|---|---|---|---|---|
| **§3.6** | `[DV CONTENT PENDING]` / `[EN CONTENT PENDING]` placeholders | **FAIL** (IS-5.5) | **PASS** | I treated the placeholders as shipped-by-accident defects. SPEC.md §3.6 **mandates them**: *"Everywhere the source documents were unreadable, seed the literal string `[DV CONTENT PENDING]` and seed the English column with `[EN CONTENT PENDING]`. Do NOT fabricate, machine-translate, or invent."* §12 repeats it, §14 confirms strike levels 4–5 and the consequence table are still pending from the customer. Verified both sides carry the matching literal: `lang/en/public.php` → `[EN CONTENT PENDING]`, `lang/dv/public.php` → `[DV CONTENT PENDING]`. **The build did exactly the right thing. AUDIT P1-11 is withdrawn.** |
| **§4 `attendances`** | Unique composite (`training_session_id`, `student_id`) | **PARTIAL — missing** | **PASS** | **My Stage 1 finding was wrong.** The constraint exists: `database/migrations/2024_01_01_000080_create_attendances_table.php:21` `$table->unique(['training_session_id','student_id'])`, live as `attendances_training_session_id_student_id_unique` (UNIQUE). See §3 for the method error. |
| **§4 `guardian_student`** | Unique composite (`guardian_id`, `student_id`) | **PARTIAL — missing** | **PASS** | Same error. Exists at `2024_01_01_000040_create_guardian_student_table.php:20`, live as `guardian_student_guardian_id_student_id_unique` (UNIQUE). |
| **§4 `agreement_signatures`** | Index (`student_id`, `status`) | **PARTIAL — "no unique constraint"** | **PASS** | Two errors compounded. The index exists (`…:29`, live as `agreement_signatures_student_id_status_index`), and SPEC.md asks for an **index**, not a unique — a student legitimately holds one signature per template version. My recommendation would have broken re-signing after a version bump. |
| **§4 `squad_student`** | One active enrolment per student | **PARTIAL — "no DB constraint"** | **PASS** | SPEC.md §4 explicitly says *"enforced in the service layer, **not with a DB constraint**."* `EnrolmentService` does exactly that. **My recommendation to add a DB constraint contradicted the contract.** The required `Index (squad_id, is_active)` is present (`…000060:20`). |
| **§7 API / §10** | Is `/api/v1` an out-of-scope build? | **UNVERIFIABLE** | **IN SCOPE — no violation** | SPEC.md §7 mandates all six endpoints by name and requires Resources that resolve `*_dv`/`*_en` by `Accept-Language`; §5 requires `HasApiTokens` on User. `README.md:154-158`'s claim was accurate. This resolves AUDIT §4.7 — see §2 below. |
| **§11 Policies** | Which models need a policy | **PARTIAL — "8 models have no policy"** | **PASS** | SPEC.md §11 enumerates **exactly five**: `StudentPolicy, SquadPolicy, TrainingSessionPolicy, AttendancePolicy, UserPolicy`. All five exist. The contract never asked for policies on Guardian, Coach, AgreementTemplate, AgreementSignature, GuardianStudent, SquadStudent, FrameworkPillar or FrameworkStrikeLevel. **AUDIT P1-13 is withdrawn.** |
| **§3.2** | Locale switch returns to the page, preserving query strings | *not assessed* | **PASS** | `LocaleController.php:20` `redirect()->back()` resolves via the referer, which carries the query string. |
| **§6** | Role/permission matrix | *not assessed* | **PASS** | `RolePermissionSeeder.php:15-38` defines all 24 permissions in §6 and assigns each role exactly the listed set. Verified name-by-name against §6 — no additions, no omissions. |
| **§8.3** | Gate fails CLOSED | **FAIL** (IS-7.7a) | **FAIL — escalated to P0** | Verdict unchanged, severity rises. SPEC.md §8.3 states it as a bolded rule and specifies the exact behaviour I had to guess at: *"guardians and students are denied portal access and shown a translated message directing them to the academy office; admins retain access so they can publish one."* Two named tests now exist for it in §13. |
| **§8.3 / §7** | `password.changed` on the agreement routes | **FAIL** (IS-3.9) | **FAIL — escalated to P0** | Verdict unchanged, severity rises. §7's route table lists the middleware triplet `(auth, role:guardian, password.changed)` verbatim, and §8.3 restates it: *"a binding agreement may not be signed while on an admin-issued password."* This is an explicit contract term, not an inference. |
| **§8.3** | Signature immutability | **FAIL** (IS-7.4) | **FAIL — escalated, and wider than I found** | I probed 3 fields. SPEC.md names **five** that may never be updated — `template_snapshot`, `signed_at`, `signed_locale`, `consents`, `agreement_template_id` — mandates the guard in the model's `updating` event, and requires `AgreementService::revoke()` as the sole writer of `status`/`revoked_at`/`revoked_reason`. That method **does not exist** (0 definitions). |
| **§8.7** | Attendance statistics authority | **FAIL** (IS-8.6) | **FAIL — my remedy was wrong** | The divergence I found is real, but I proposed extracting into the existing `AttendanceService`. SPEC.md §8.7 and §11 require a **separate `AttendanceStatisticsService`** as SOLE authority, with `late`/`excused` treatment read from `config('academy.attendance')`. That service does not exist and that config key does not exist. |
| **§3.4** | Thaana webfont | **FAIL** (P1-9) | **FAIL — confirmed verbatim, plus a missing deliverable** | SPEC.md §3.4 independently states every element I flagged: licensed, bundled locally, `.font-thaana` class, *"set as the body font ONLY when locale is `dv`… never as the global `sans` family"*, no CDN, *"Do not bundle a proprietary font without a licence permitting web redistribution."* `tailwind.config.js:13` sets it as global `sans` — a direct violation. §14 additionally requires `FONT-LICENCE.md`, which does not exist. **Note:** SPEC.md never names a specific face; "MV Faseyha" came from your Stage 1 instruction, not the contract. |

---

## 2. AUDIT §4.7 — OUT-OF-SCOPE INVENTORY (now answerable)

SPEC.md §10 forbids: public/self-service registration; behaviour check-ins and pillar ratings; report-card uploads; badges/rewards/MVP; the live strike-logging engine and its push alerts; time-out timers; the parent "behaviour flag" button; in-app chat; the meeting scheduler; the Flutter app; PDF generation; SMS gateway; push notifications.

**Result: ZERO violations.** I swept `app/ routes/ resources/views/ database/` for 30 terms. Every hit was a false positive:

| Term | Hits | Disposition |
|---|---|---|
| `badge` | 19 files | `<x-status-badge>` UI component (§9 requires status badges on 8 screens). Not a rewards badge. |
| `message` / `Message` | 32 files | `:messages=` on `x-input-error`, and `ValidationException::withMessages()`. |
| `meeting` | 5 files | `triggers_meeting` column — **explicitly required by SPEC.md §4** `framework_strike_levels`. In scope. |
| `push` | 3 files | Alpine `this.clauses.push(...)` in the clause repeater. |
| `Notification` | 1 file | Laravel's framework-default `Notifiable` trait on `User`. |
| `register` / `Register` | 7 files | `registered_at` column, `StudentRegistrationService`, `AppServiceProvider::register()`. |
| pdf, dompdf, snappy, chat, sms, twilio, firebase, fcm, flutter, reward, mvp, checkin, rating, strike_log, timeout_timer, signup | **0** | Absent entirely. |

The agreement download is an HTML print view (`AgreementController::download` returns a Blade view), which is what §7 requires — *"HTML print view in the signed locale; not PDF in Phase 1."* Correct.

**No deletion work is required in WP-6.**

---

## 3. RETRACTIONS — STAGE 1 ERRORS AND THEIR CAUSE

Per standing rule 8, stated in full rather than quietly corrected.

### Retraction 1 — missing database constraints (AUDIT P2-16)

**I previously reported** that `attendances`, `guardian_student` and `agreement_signatures` lacked uniqueness constraints, and that `squad_student` lacked a one-active-enrolment constraint — four sub-findings, filed as P2-16, with the note *"All four invariants are enforced in PHP only."*

**That was wrong. The correct figure is zero missing constraints of the four.** Three exist and are live; the fourth is explicitly forbidden by the contract.

**Method error that caused it:** in Stage 1 I read the schema with `SHOW COLUMNS`, whose `Key` field reports only `PRI`/`UNI`/`MUL` for the **leading column** of an index. A composite `UNIQUE(training_session_id, student_id)` renders as `MUL` on the first column and nothing on the second — indistinguishable from an ordinary FK index. I never ran `SHOW INDEX`, and I did not cross-check the migration source. Both would have caught it immediately:

```
attendances_training_session_id_student_id_unique   seq=1 col=training_session_id unique=YES
attendances_training_session_id_student_id_unique   seq=2 col=student_id          unique=YES
guardian_student_guardian_id_student_id_unique      seq=1 col=guardian_id          unique=YES
guardian_student_guardian_id_student_id_unique      seq=2 col=student_id           unique=YES
```

**Consequence had this gone unchecked:** WP-4 would have written a migration adding a duplicate unique index to `attendances` (MySQL error 1061), and a constraint on `squad_student` that directly contradicts SPEC.md §4.

### Retraction 2 — policy coverage (AUDIT P1-13)

**I previously reported** *"8 of 13 models have no policy"* as a P1 spec violation. **That was wrong.** SPEC.md §11 specifies exactly five policies; all five exist and are correctly auto-discovered. There is no requirement for the other eight. The finding is withdrawn, not downgraded.

**Method error:** I inferred a "every model needs a policy" rule from the audit prompt's phrasing *"List every Policy that exists and every model that has none."* That instruction asked for an inventory; I converted an inventory into a violation without a contract to justify it. Exactly the inflation the Stage 1 prompt warned against.

### Retraction 3 — placeholder content (AUDIT P1-11)

**I previously reported** `[DV CONTENT PENDING]` as a P1 defect, *"Placeholder text ships to the public contact page."* **That was wrong** — it is a mandated behaviour under §3.6, and the build implemented it correctly on both language sides. Withdrawn.

**Method error:** the inferred spec had no way to distinguish "unfinished work" from "deliberately marked pending content", so I defaulted to treating a placeholder as a defect. This is the clearest illustration of D1's warning in AUDIT.md: a reconstruction cannot recover a policy that produces output indistinguishable from a bug.

### Net effect on the Stage 1 backlog

| | Count |
|---|---|
| AUDIT.md §7 items | 26 |
| Withdrawn as not-a-defect | 3 (P1-11, P1-13, P2-16) |
| Verdict-changed to PASS elsewhere | — |
| Carried forward | 23 |
| New items from SPEC.md requirements never assessed | 13 |
| **BACKLOG.md total** | **36** |

---

## 4. SPEC.md REQUIREMENTS THE AUDIT NEVER ASSESSED

These had no counterpart in the inferred spec. Severities assigned here; all carried into BACKLOG.md.

| # | SPEC ref | Requirement | Status | Severity |
|---|---|---|---|---|
| N1 | §8.7, §11 | `AttendanceStatisticsService` exists as sole authority; `config('academy.attendance')` keys | **ABSENT** — neither exists | **P1** |
| N2 | §8.3, §11 | `AgreementService::revoke()` as sole writer of `status`/`revoked_at`/`revoked_reason` | **ABSENT** — 0 definitions | **P1** |
| N3 | §14, §3.4 | `FONT-LICENCE.md` documenting the Thaana font licence position | **ABSENT** | **P1** |
| N4 | §3.5 | Fallback renders show a translated *"not available in this language"* hint | **PARTIAL** — `isTranslationFallback()` used at only 2 of ~61 `translated()` render sites (`public/home.blade.php:19`, `public/framework.blade.php:12`) | **P2** |
| N5 | §3.5 | Admin forms: **both** language fields required, *"validation rejects saving one without the other"* | **FAIL** — `venue_dv`/`venue_en` are `nullable` in `StoreSquadRequest:21-22` and `StoreSessionRequest:21-22`; one can be saved without the other | **P2** |
| N6 | §8.2 | Linking to an existing guardian phone must *"surface a translated notice to the admin"* | **FAIL** — `GuardianController::store:38-40` discards the link-vs-create signal and always flashes `admin.guardian.created` | **P2** |
| N7 | §12 | *"No N+1 queries — eager load and assert it in tests"* | **FAIL** — no query-count test exists; N+1s confirmed in AUDIT §4.6 | **P2** |
| N8 | §8.6, §12 | Photos on the **`private`** disk; `.env.example` carries *"the private disk config"* | **PARTIAL** — no disk named `private`; code uses `local`, whose root is `storage/app/private` with `'serve' => true`. Privacy is genuinely met; the name is not | **P3** |
| N9 | §4 | `index_number` regex `/^[A-Z0-9\-]{3,20}$/` | **PARTIAL** — `StoreStudentRequest:22` uses `/^[A-Za-z0-9\-]{3,20}$/`; `StudentRegistrationService:43` uppercases on write, so stored data is correct but the form accepts input the contract rejects | **P3** |
| N10 | §8.1 | Throttle *"5 failed attempts per **username** per minute"* | **PARTIAL** — `LoginRequest:78` keys on `username\|ip`, so the same username from a second IP gets a fresh budget | **P3** |
| N11 | §12 | No commented-out code anywhere | **FAIL** — `2026_08_11_084916_create_permission_tables.php:24,34` `// $table->engine('InnoDB');` (vendor-published stub, still in our tree) | **P3** |
| N12 | §3.4 | Icons, chevrons, progress bars and directional affordances mirror under RTL | **UNASSESSED** — requires visual verification in both directions; no automated check exists | **P2** |
| N13 | §9 | 29 named UI screens with specified content | **UNASSESSED** — Stage 1 audited routes and data flow, not per-screen content against §9 | **P2** |

**Assessed clean, no backlog item:** §3.4 Western Arabic numerals (no Eastern-Arabic digits anywhere in `lang/`); §3.4 `FormatsDates` (used in 23 views; the 2 raw `->format()` calls are `datetime-local` input values, not display); §3.3 domain split (14 required domains all present; `public` and `pagination` are additional, which the contract does not forbid); §12 no `dd()`/`dump()`/`var_dump()`/`print_r()` (zero hits under a precise sweep — my first sweep's `ray(` pattern produced 20 false positives on `in_array(`/`toArray(`, retracted); §6 permissions; §10 scope.

---

## 5. SPEC.md §13 NAMED-TEST GAP

Extracted programmatically from §13; diffed against all 43 test methods in `tests/Feature/`.

- **Explicitly named in §13: 58**
- **Existing in repo: 43**
- **Named but absent: 18**
- Plus §13's unnamed requirement *"One happy-path and one wrong-role test per `/api/v1` endpoint in §7"* — 6 endpoints × 2 = **12 further tests**, none of which exist.
- **Total missing: 30.**

| # | Missing test | Blocks |
|---|---|---|
| 1 | `AgreementGateTest::test_gate_denies_when_no_current_template_exists` | P0-4 |
| 2 | `AgreementGateTest::test_admin_retains_access_when_no_current_template_exists` | P0-4 |
| 3 | `AgreementGateTest::test_guardian_must_change_password_before_signing` | P0-5 |
| 4 | `AgreementSignatureTest::test_signed_agreement_fields_cannot_be_updated` | P0-3 |
| 5 | `AgreementSignatureTest::test_revocation_is_the_only_mutation_path` | P0-3 |
| 6 | `AgreementSignatureTest::test_revoked_signature_no_longer_satisfies_the_gate` | P0-3 |
| 7 | `AttendanceStatisticsTest::test_all_roles_see_identical_percentage_for_same_student` | P1-A |
| 8 | `AttendanceStatisticsTest::test_late_treatment_follows_config` | P1-A |
| 9 | `AttendanceStatisticsTest::test_excused_is_excluded_from_denominator` | P1-A |
| 10 | `AttendanceStatisticsTest::test_no_percentage_is_computed_outside_the_service` | P1-A |
| 11 | `RouteCoverageTest::test_every_non_public_route_rejects_guests_and_wrong_roles` | WP-1 |
| 12 | `StudentPhotoTest::test_signed_photo_url_renders_for_authorised_viewer` | P0-1 |
| 13 | `StudentPhotoTest::test_unsigned_photo_url_is_rejected` | P0-1 |
| 14 | `StudentPhotoTest::test_guardian_cannot_load_another_childs_photo` | P0-1 |
| 15 | `StudentPhotoTest::test_photo_route_is_not_publicly_reachable` | P0-1 |
| 16 | `ApiLocaleTest::test_accept_language_switches_resource_language` | P0-2 |
| 17 | `LocalisationTest::test_thaana_font_class_applies_only_in_dv_locale` | P1-B |
| 18 | `LocalisationTest::test_no_proprietary_font_binary_in_repository` | P1-B |
| 19–30 | 12 × `/api/v1` happy-path + wrong-role | P0-2 |

**Present but not named in §13 (3) — keep, they are harmless additions:** `AgreementTemplateTest::test_publish_succeeds_when_both_languages_complete`, `AuthenticationTest::test_login_screen_can_be_rendered`, `AuthenticationTest::test_users_can_logout`.

**Named tests that exist but §13 requires to be REAL, not decorative** — three are currently decorative and are addressed in WP-1: the three `AuthorizationTest` cases asserting via `->can()` only. §13 flags this explicitly: *"Authorisation — must assert on HTTP responses, not on `->can()` alone."* The contract independently confirms the Stage 1 finding.

---

## 6. WHAT DID NOT CHANGE

For completeness — the audit's core conclusions survive contact with the contract:

- **P0-1 (photos 403)** — SPEC.md §8.6 requires signed URLs *"from a single shared construction site"* and forbids `onerror` suppression, in those words. Confirmed exactly as found.
- **P0-2 (API dead)** — §5 requires `HasApiTokens`; §7 requires all six endpoints. Confirmed.
- **REPAIR over rebuild** — reinforced. The schema now scores *better* than the audit said (all required indexes present), §6 permissions are exact, §10 scope is clean, and §3.6 placeholder handling was correct all along. The gap is concentrated in five service/middleware defects and a missing test surface.
- **The root cause remains verification, not architecture.** SPEC.md §13 names 58 tests; 30 do not exist, and every one of the P0s sits squarely in that gap.
