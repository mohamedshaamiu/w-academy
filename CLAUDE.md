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

One authorised exception: the **public marketing copy** (hero, vision, mission,
values, about, the pillar descriptions in `FrameworkPillarSeeder`) was written
for the demo at the customer's instruction, in both languages. Every such
string is marked `DEMO COPY — CLIENT TO CONFIRM` in the line above it and the
deviation is recorded as BACKLOG-NEW.md NEW-5. Do **not** revert those to
pending markers, and do **not** extend the exception to any other surface —
agreement bodies, strike-ladder policy text and the academy's address and
phone number stay pending until the customer supplies them.

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

## Demo deployment

Live at **https://demos.devcitymv.com/w-academy/** on Devcity's shared demo
box (SSH host alias `devcitymv`, CyberPanel/OpenLiteSpeed). `README.md` §
"Deployment" describes the generic first-time install; this section is the
redeploy procedure for that specific slot.

- App dir `/home/demos.devcitymv.com/public_html/w-academy/`, site user
  `demos8016`, DB `demos_wacademy`.
- `APP_ENV=production`, `APP_DEBUG=false`,
  `APP_URL=https://demos.devcitymv.com/w-academy` — **no `/public` suffix**.
  The vhost routes traffic into `public/` transparently.
- Server toolchain is on `PATH` for `demos8016`: PHP 8.3, node 20, composer.
- It is a real git checkout, so `git fetch` + `git checkout` is the deploy —
  no tarballs.

**Never run composer/npm/artisan as root** — always drop to the site user:
`su - demos8016 -s /bin/bash -c '...'`. Root-created files leave the LSAPI
worker unable to write logs, cache and sessions.

```bash
git checkout -- package-lock.json   # drifts server-side; discard before switching
git fetch origin && git checkout <branch>
composer install --no-dev --optimize-autoloader --no-interaction
npm install && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan config:cache && php artisan view:cache
```

Three things not to do:

- **Do not re-seed.** The demo already holds its seeded data. Reach for
  `db:seed` only when the seeders themselves have actually changed, and then
  only after checking the seeder is idempotent.

  `FrameworkPillarSeeder` is the one that has changed since the demo was first
  seeded — it now carries real pillar names and demo descriptions instead of
  `[CONTENT PENDING]`, and the public site is built around them. It is
  idempotent on `code`, so it is safe to re-run on its own:

  ```bash
  php artisan db:seed --class=FrameworkPillarSeeder --force
  ```

  Nothing else needs re-seeding. Never run a bare `db:seed` here — that would
  drag in `DemoDataSeeder` and the rest.
- **Do not `git clean -fd`.** The root `.htaccess` (`RewriteRule ^$ public/`)
  is untracked and survives branch switches; cleaning kills the
  bare-directory redirect.
- **Do not run `route:cache` (nor `artisan optimize`, which includes it) on
  this demo.** The app is served from a *subdirectory* (`/w-academy/`), and a
  cached route table breaks the bare directory URL: `GET
  https://demos.devcitymv.com/w-academy/` returns **405 Method Not Allowed**
  ("The GET method is not supported for route /. Supported methods: HEAD.")
  while every deeper URL — `/login`, `/framework`, even `/w-academy/index.php`
  — still returns 200.

  It is the route cache itself, not a stale one: clearing it gives 200, and a
  freshly built cache reproduces the 405 immediately. With routes cached,
  `CompiledRouteCollection::match()` puts the request through
  `requestWithoutTrailingSlash()`, which rewrites `REQUEST_URI` from
  `/w-academy/` to `/w-academy`. That collides with the subdirectory base
  path, the compiled matcher fails to match, and Laravel's fallback then
  reports only the *other* verb it can find for `/` — hence the misleading
  `Allow: HEAD` on what is really a routing miss. Confirmed on Laravel 11.55.0.

  `config:cache` and `view:cache` are unaffected — keep those. No other app on
  the demo box route-caches, which is why w-academy was the only one hit.

`storage:link` is not needed — photos and signatures stay on the private disk
behind the signed `students.photo` route, and nothing is served from the
`public` disk.

Verify after every deploy: the bare root `/w-academy/` is 200 (**not** 405 —
see the route-cache note above; the trailing slash matters, so test that exact
URL), `/about`, `/framework` and `/contact` are 200, `/login` is 200 and
carries `dir="rtl"`, `/dashboard` 302s to `/login`, and the `app-*.css` hash in
the served HTML matches what vite just printed — that last one is what catches
a stale cache.

The public site is the demo's first impression, so also check it renders the
seeded pillar names rather than `[CONTENT PENDING]`:

```bash
curl -s https://demos.devcitymv.com/w-academy/ | grep -c 'CONTENT PENDING'
```

Expect **4** — the academy address and phone, each rendered twice (contact
block and footer). More than that means `FrameworkPillarSeeder` has not been
re-run.

## Current state

All P0 and P1 items from the remediation are closed. Suite is green
(91 tests / 1080 assertions, plus the one pre-existing incomplete for
BACKLOG-NEW.md NEW-1), Pint clean.

The public site was rebuilt to be informative: a design-led hero slider,
academy intro, vision & mission, values, the four pillars, "how to join", an
enriched contact block, a richer footer, and a new `/about` page. Sections live
in `resources/views/public/partials/`; see README.md § "The public site" and
`PublicPagesTest`. `/about` is outside SPEC.md §7's route table — recorded as
BACKLOG-NEW.md NEW-6.

`remediation/phase-1` is pushed and is what the demo runs. **`main` is still
the original pre-remediation build** — the two have diverged and no PR has
been opened. Merge before treating `main` as current.

Outstanding, and all of these need the customer rather than code:

- **Dhivehi copy** for the "no agreement published" screen —
  `lang/dv/agreement.php` `unavailable.*` is `[DV CONTENT PENDING]`.
- **MV Faseyha licence text** — the font is bundled and working; the licence
  itself isn't evidenced in writing. See `FONT-LICENCE.md`.
- **Academy address and phone** — `public.contact.address_value` /
  `phone_value` are the only `[CONTENT PENDING]` left on the public site. They
  render twice each (contact block and footer).
- **Sign-off on the demo public copy** — everything marked `DEMO COPY —
  CLIENT TO CONFIRM`; see BACKLOG-NEW.md NEW-5 and
  `grep -rn "DEMO COPY" lang/ database/seeders/`.

`BACKLOG.md` holds the remaining optional items and `BACKLOG-NEW.md` the ones
found during remediation. Known and deliberately deferred: N+1 on the guardian
dashboard, `coach_no` generated as `max(id)+1`, and `venue_dv`/`venue_en`
being savable one without the other. None bites at this scale.

`AUDIT.md` and `DELTA.md` record how the original delivery failed and what
changed once SPEC.md was reconciled — useful background, not current work.
