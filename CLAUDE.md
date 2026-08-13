# CLAUDE.md — W-Academy

Guidance for Claude Code working in this repository.

## What this is

A bilingual (Dhivehi/English) Laravel 11 web app for W-Academy, a youth
football academy in the Maldives. Free CSR project delivered by Devcity.
Phase 1 only: auth, admin-created records, the discipline agreement, squads,
sessions, attendance, and a read-only framework reference.

**`SPEC.md` at the repo root is the acceptance contract and the sole authority
on what Phase 1 requires.** Where anything — this file included — disagrees
with SPEC.md, SPEC.md wins. Read §10 before building anything new: it lists
what must *not* be built in Phase 1.

Small client, small scale. Dozens of students, not thousands. Prefer the
simple fix; say plainly when a concern is theoretical rather than acting on it.

## Stack

PHP 8.2 · Laravel 11 · MySQL 8 · Blade + Tailwind (Vite) · Alpine.js
spatie/laravel-permission · spatie/laravel-activitylog · intervention/image ·
laravel/sanctum (for the `/api/v1` surface the later Flutter app will use).

No package outside SPEC.md §2 without a justification recorded in `README.md`.

## Commands

```bash
composer install && npm install
php artisan migrate:fresh --seed     # MySQL; DemoDataSeeder only when APP_ENV=local
npm run build
php artisan test                     # sqlite :memory:, ~2-3 min — background it
./vendor/bin/pint --test             # must be clean
```

Default admin after seeding: username `7900000`, password `WAcademy@2026`
(forces a password change on first login).

The full suite is slow on Windows. Run it backgrounded and poll the log rather
than blocking. If PHP itself starts taking minutes, that's a machine I/O stall,
not the code — check `time php -r 'echo 1;'` and look at the CPU columns.

## The five rules that matter

These are where the original build went wrong. Breaking one is a spec failure,
not a style preference.

**1. Nothing user-facing is hardcoded.** Every string goes through `__()` or
`@lang`. `lang/dv` and `lang/en` must have identical key sets — enforced by
`LocalisationTest::test_lang_dv_and_en_have_identical_key_sets`. Content
columns are `*_dv`/`*_en` pairs rendered via
`HasTranslatedAttributes::translated('name')`, never `$model->name_dv`.

If Dhivehi copy hasn't been supplied, seed the literal `[DV CONTENT PENDING]`
(and `[EN CONTENT PENDING]`). **Do not invent or machine-translate Dhivehi** —
SPEC.md §3.6. Those placeholders are correct, not defects.

**2. Layout mirrors.** Use logical Tailwind utilities (`ms-`, `me-`, `ps-`,
`pe-`, `text-start`, `text-end`), never `ml-`/`mr-`/`pl-`/`pr-`/`text-left`/
`text-right`. `dir` is locale-driven in every layout.

The Thaana face (`MV Faseyha`, bundled in `resources/fonts/`) is applied via
the `font-thaana` utility **only when the locale is `dv`** — never as the
global `sans` family, or English renders in a Thaana font. See
`FONT-LICENCE.md`; never bundle a font without recording its licence.

**3. The agreement gate is the core Phase 1 rule.** `EnsureAgreementSigned`
covers the guardian portal, the student portal, and `/api/v1/children` +
`/schedule`. It **fails closed**: no current template means no access, except
for admins who must still be able to publish one. It governs guardians and
students only — coaches are not party to the agreement.

Signatures are immutable. `AgreementSignature` freezes every column in its
`updating` event; the three revocation columns open only inside
`AgreementService::revoke()`. Corrections are made by revoking and re-signing,
never by editing.

**4. Child data is protected by Policies, not query scoping.** SPEC.md §8.6 is
explicit that scoping alone is a failure. Student photos are on the private
disk and served only through the signed, policy-checked `students.photo` route
— build those URLs with `Student::photoUrl()`, which is the single
construction site. Never suppress a media failure with `onerror`; the
`<x-student-photo>` component renders a visible placeholder instead.

**5. Business rules live in services.** `app/Services/` is the authority; no
business logic in Blade, no raw SQL. `AttendanceStatisticsService` is the
**sole** place any attendance count or percentage is computed — enforced by a
grep-based test. `late`/`excused` treatment is read from
`config('academy.attendance')`, never hardcoded.

## Testing

`SPEC.md` §13 names the required tests. A test that only asserts
`assertStatus(200)` is decorative and doesn't count. Authorisation tests must
assert on real HTTP responses, not `->can()` alone — a passing policy proves
nothing about whether any route consults it.

`RouteCoverageTest` is the safety net: it walks every route and fails if one
escapes coverage, so a newly added route can't slip past the guest, wrong-role
and pending-password-change checks.

**Gotcha:** `actingAs()` leaves the guard resolved for the rest of the test.
Call `$this->app['auth']->forgetGuards()` before a request that must run
unauthenticated, or run the guest pass before any authentication. This has
produced convincing false findings twice.

## Layout

```
app/Concerns/HasTranslatedAttributes.php   translated('base') for *_dv/*_en
app/Enums/                                 backed enums, each with label()
app/Http/Middleware/                       SetLocale, SetApiLocale,
                                           EnsureAgreementSigned, EnsurePasswordChanged
app/Policies/                              exactly the 5 in SPEC.md §11
app/Services/                              all business rules
config/academy.php                         academy policy knobs
lang/{dv,en}/                              identical key sets
```

## Current state

All P0 and P1 items from the remediation are closed. Suite is green
(87 tests / 615 assertions), Pint clean.

Outstanding, and both need the customer rather than code:

- **Dhivehi copy** for the "no agreement published" screen —
  `lang/dv/agreement.php` `unavailable.*` is `[DV CONTENT PENDING]`.
- **MV Faseyha licence text** — the font is bundled and working; the licence
  itself isn't evidenced in writing. See `FONT-LICENCE.md`.

`BACKLOG.md` holds the remaining optional items and `BACKLOG-NEW.md` the ones
found during remediation. Known and deliberately deferred: N+1 on the guardian
dashboard, `coach_no` generated as `max(id)+1`, and `venue_dv`/`venue_en`
being savable one without the other. None bites at this scale.

`AUDIT.md` and `DELTA.md` record how the original delivery failed and what
changed once SPEC.md was reconciled — useful background, not current work.
