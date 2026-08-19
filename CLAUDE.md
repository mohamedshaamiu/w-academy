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

Three kinds of content now live side by side. Know which you are touching
before you edit it:

| Kind | Where | Rule |
|---|---|---|
| **Customer's own words** | Agreement title/body + discipline clause, all three strike levels, the four pillar *descriptions* — all `_dv` | Transcribed from their document. Do not reword, "improve" or translate. Fix only against the source PDF. |
| **DEMO COPY** | Public marketing copy: hero, intro, vision, mission, values, about, how-to-join, footer, the coaches section, and the pillar descriptions' `_en` side | Authored for the demo at the customer's instruction. Marked `DEMO COPY — CLIENT TO CONFIRM` on the line above. Recorded as BACKLOG-NEW.md NEW-5. Do **not** revert to pending markers. |
| **Genuinely pending** | Every `_en` counterpart of the customer's Dhivehi, the photo-consent clause (both languages), `agreement.unavailable.*` (dv), academy address and phone | Stays `[DV/EN CONTENT PENDING]` until the customer supplies it. |

The demo-copy exception does **not** extend to any new surface. In particular
the agreement and strike ladder now hold real Dhivehi, so the temptation to
"complete" them by translating is stronger, not weaker — don't. An English
column reading `[EN CONTENT PENDING]` next to real Dhivehi is the correct
state, and `HasTranslatedAttributes::isTranslationFallback()` already renders
the "not available in this language" hint for it.

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

**Coach photos are the deliberate counter-example, not a hole in this rule.**
They sit on the same private disk but are served by `coaches.photo`, which
carries **only** the `signed` middleware — no auth, no policy. That is
correct: the homepage coaches section is public, and a coach is academy staff
who consented to being on the marketing site, not a minor. §8.6 governs child
data and does not reach them. The signature exists to stop URL enumeration,
nothing more. Build the URLs with `Coach::photoUrl()` (same single-construction
-site discipline), and the same no-`onerror` rule applies — a coach without a
photo gets a crest-palette initial. Proven by `CoachPhotoTest`; the route is
allowlisted with this reasoning in `RouteCoverageTest::PUBLIC_ROUTE_NAMES`.
Before you "fix" it by adding auth, read BACKLOG-NEW.md NEW-9 — that would
break the public homepage.

**5. Business rules live in services.** `app/Services/` is the authority; no
business logic in Blade, no raw SQL. `AttendanceStatisticsService` is the
**sole** place any attendance count or percentage is computed — enforced by a
grep-based test. `late`/`excused` treatment is read from
`config('academy.attendance')`, never hardcoded.

## Customer documents

`customer documents/` (tracked, at the repo root) holds what W-Academy
actually supplied, in August 2026. Treat it as the source of truth for
content, the way SPEC.md is for behaviour.

| File | What it is | Status |
|---|---|---|
| `logo.jpeg` | The academy crest — black/gold, "W-ACADEMY EST. 2026" | Processed into site assets, see § Branding |
| `W CDMY.pdf` | Code of conduct, the 4 pillars, the 5 golden rules, the 3-strike ladder, and the parental agreement form. **Dhivehi only** | Transcribed and seeded |
| `U6 Physical Check-Up Chart & Assessment Sheet.pdf` | Under-6 movement/health assessment form (English) | **Not built** — no SPEC coverage, BACKLOG-NEW.md NEW-8 |
| `TRANSCRIPTION-DV.md` | The transcription of `W CDMY.pdf`, written by this project | The thing to read/edit; not the PDF |

**The PDF cannot be copy-pasted.** It is a Word export whose text layer is
broken: every Thaana run is an embedded image, and the ToUnicode map collapses
almost every glyph to `ޑ`. `pdftotext`, PyMuPDF `get_text()` and the embedded
font's cmap all return garbage — verified, don't spend time re-discovering it.
Tesseract's `div` model is no better on this render.

What did work, if another Dhivehi document ever arrives:

1. Render pages at high zoom with PyMuPDF (`fitz.Matrix(6,6)` in strips, or
   10–18× for individual words) and read them.
2. Recover the per-word **character counts** from the broken text layer — the
   run lengths survive even though the letters don't. That turns transcription
   into a checkable exercise rather than a guess.
3. Verify by re-rendering your transcription in MV Boli
   (`C:\Windows\Fonts\mvboli.ttf`) directly beneath the original crop and
   comparing. Pillow has no libraqm here, so reverse the combining-mark
   clusters manually before drawing — that is enough to compare shapes.

`TRANSCRIPTION-DV.md` records the result and marks with `؟` the handful of
words that survived every check but still deserve a native reader. **Those
flags are honest uncertainty, not defects — do not silently resolve them.**

Where it landed:

- `AgreementTemplateSeeder` — title, full body, discipline-acknowledgement
  clause (`_dv`).
- `StrikeLevelSeeder` — all three levels' label/type/action/parent-role
  (`_dv`), with the escalation flags matched to the document: 3–5 min time-out
  at strike 1, parent meeting/call at strike 2, suspension at strike 3, parent
  alerted at every level.
- `FrameworkPillarSeeder` — the four pillar descriptions (`_dv`).

All three are idempotent (`version`, `level`, `code`) and safe to re-run.

## Branding

The crest is the customer's `logo.jpeg`, centre-cropped and circle-masked into:

```
public/images/crest.png        320px, used in every layout
public/favicon.ico             16/32/48px multi-size
public/apple-touch-icon.png    180px
```

Regenerate with Pillow from `customer documents/logo.jpeg` if the customer
sends a new crest; don't hand-edit the derived files. `resources/views/
partials/favicons.blade.php` is the single `<link>` block, included by all
three layouts (`public`, `app`, `guest`).

The crest replaced a CSS-only "W" lettermark in the public header and footer,
the portal top bar and sidebar, and the login screen. `resources/views/
components/application-logo.blade.php` is still the stock Laravel SVG and is
referenced nowhere — dead code, safe to delete or repoint.

Palette stays navy `#0B1F3A` / gold `#C9A227` (SPEC.md §9), which is what the
crest is built from.

## Photography

`client photos/` (repo root) holds the seven photographs the academy supplied
on 18 Aug 2026 — squad shots on the futsal pitch, training at sunset, and beach
sessions. Same rule as the crest: the originals are the source, the web assets
are **derived and regenerated, never hand-edited**.

```
client photos/{1..7}.png          the originals — GITIGNORED, local only
public/images/photos/<slug>-{800,1600}.{webp,jpg}   committed
```

**The originals are deliberately not in the repo** (23 MB of phone PNGs, kept
out at the customer's request — `.gitignore`). This is the one place the
project departs from how `customer documents/` works, so a fresh clone can
*serve* the photography but cannot *regenerate* it. Get the originals back from
the customer before changing quality, dimensions or the crop.

Regenerate with Pillow: flatten RGBA onto white, LANCZOS to 1600 and 800 wide,
WebP q80 / JPEG q82 progressive, EXIF dropped. The originals are phone photos
carrying location data, so stripping it is the point, not a side effect.

`resources/views/components/site-photo.blade.php` is the **single construction
site** for these URLs — the marketing counterpart of `Student::photoUrl()`. It
holds the slug → intrinsic-size map, emits `<picture>` with a WebP source and a
JPEG fallback, and declares width/height so nothing shifts as photos land. Add
a photo by adding the renditions *and* the map entry; an unknown name throws
rather than rendering a broken image. No `onerror`, same as rule 4.

**Alt text is empty on every one of them, and that is correct.** Each photo
sits beside a heading that already carries the meaning, so a description adds
nothing — and writing one would mean inventing Dhivehi copy the customer has
not supplied (rule 1, SPEC.md §3.6). A `[DV CONTENT PENDING]` alt attribute
would be worse than none. The `/about` gallery band is captionless for the same
reason and is labelled from the existing `public.hero.label` key.

The same constraint shaped the whole change: **no new lang keys were added**,
so dv/en key sets stayed identical on their own and the deployed
`CONTENT PENDING` count stays at 4.

`SitePhotoTest` is the safety net — every declared rendition exists at its
declared size, every name used in a view resolves, and nothing builds an
`images/photos` path outside the component.

White copy sits over photographs in the hero, the three page heroes, the CTA
band and the login screen. The navy scrims there are **load-bearing for
contrast**, not decoration; keep them if you change the imagery.

**These seven are approved for publication** — confirmed 19 Aug 2026. They show
identifiable minors, so that approval is what the publication rests on; do not
add an eighth photograph on the assumption it is covered.

Separately, the agreement's photo-consent clause is still `[CONTENT PENDING]`
in both languages (BACKLOG-NEW.md NEW-7). That gap is about **future** intakes
being covered by the agreement rather than by a side conversation, and it is
still owed.

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
customer documents/                        what the customer supplied + TRANSCRIPTION-DV.md
public/images/crest.png                    academy crest, derived from logo.jpeg
resources/views/partials/favicons.blade.php  single favicon <link> block
resources/views/public/partials/           the public site's composable sections
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

  Three content seeders have changed since the demo was first seeded, all of
  them idempotent (`code`, `level`, `version`) and safe to re-run **by name**:

  ```bash
  php artisan db:seed --class=FrameworkPillarSeeder --force
  php artisan db:seed --class=StrikeLevelSeeder --force
  php artisan db:seed --class=AgreementTemplateSeeder --force
  ```

  Nothing else needs re-seeding. Never run a bare `db:seed` here — that would
  drag in `DemoDataSeeder` and the rest.

  Note `AgreementTemplateSeeder` writes version 1 in place. That is fine while
  the demo's signatures are throwaway, but on a real deployment **changing a
  published agreement's body is not a seeder's job** — signatures are immutable
  and bound to the version they signed (rule 3). Publish a new version through
  the admin UI instead.
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

Since the customer content landed, also confirm on the deployed site:

- the homepage carries `images/crest.png` and `/favicon.ico` is 200;
- the photography is served — `curl -sI .../w-academy/images/photos/
  training-stretch-1600.webp` is 200, and the homepage HTML names all seven
  slugs (`curl -s ... | grep -o 'images/photos/[a-z-]*' | sort -u | wc -l`
  expects 7). These are tracked files under `public/`, so `git checkout` is the
  whole deploy — but a 404 here means the checkout missed them;
- the homepage coaches section names the seeded coaches;
- `/framework` renders the customer's Dhivehi strike ladder — grep the live
  HTML for `ސަސްޕެންޝަން` (suspension) and `ޓްރެއިނިންގ` (from the sport
  pillar). Their absence means the content seeders were skipped.

Last deployed 18 Aug 2026 (`f4103d0`) and verified against all of the above.

## Current state

All P0 and P1 items from the remediation are closed. Suite is green
(104 tests / 1222 assertions, plus the one pre-existing incomplete for
BACKLOG-NEW.md NEW-1), Pint clean.

The public site was rebuilt to be informative: a design-led hero slider,
academy intro, vision & mission, values, the four pillars, a coaches section,
"how to join", an enriched contact block, a richer footer, and a new `/about`
page. Sections live in `resources/views/public/partials/`; see README.md §
"The public site" and `PublicPagesTest`. `/about` is outside SPEC.md §7's
route table — recorded as BACKLOG-NEW.md NEW-6.

**The academy's photography landed 18 Aug 2026** and now runs through the
public site and the login screen — see § "Photography". It replaced the
design-led, image-free hero. No new lang keys were needed, so nothing about the
translation state changed; guardian consent for public use is the open item.

**Customer content landed 18 Aug 2026** — see § "Customer documents" above for
the full picture. In short: the crest is now the site's branding, the Dhivehi
agreement/strike-ladder/pillar copy is real customer text, the English side of
all of it is still owed, and the U6 assessment sheet was deliberately not
built (BACKLOG-NEW.md NEW-7/NEW-8).

**Coaches on the homepage** (customer request, same day). The section reads
the coach records admins already maintain — name, specialisation, joined year,
optional photo. It renders nothing when there are no coaches. Files:

```
database/migrations/2026_08_18_000010_add_photo_path_to_coaches_table.php
app/Models/Coach.php                            hasPhoto(), photoUrl()
app/Http/Controllers/CoachPhotoController.php   signed, no auth — see rule 4
routes/web.php                                  coaches.photo
resources/views/public/partials/coach-cards.blade.php
resources/views/admin/coaches/{create,edit}.blade.php   upload field
tests/Feature/CoachPhotoTest.php
```

Uploads go to the private disk under `coaches/` via the admin coach form
(`enctype="multipart/form-data"` — easy to forget when adding fields there).
Recorded as BACKLOG-NEW.md NEW-9 because the route sits outside SPEC.md §7's
table, the same shape of deviation as `/about` (NEW-6).

`remediation/phase-1` is pushed (`f4103d0`) and is what the demo runs.
**`main` is still the original pre-remediation build** — the two have diverged
and no PR has been opened. Merge before treating `main` as current.

Outstanding, and all of these need the customer rather than code:

- **English translations** of the agreement body/title, the discipline-clause
  label and the strike ladder (`*_en` columns), plus the photo-consent clause
  in both languages — BACKLOG-NEW.md NEW-7.
- **A Dhivehi proofread** of the `؟`-flagged words in
  `customer documents/TRANSCRIPTION-DV.md`.
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
- **Coach photos and real coach records** — the homepage section works but the
  demo only holds `DemoDataSeeder`'s two placeholder coaches, none with a
  photo. Nothing to build; the academy adds them through Admin → Coaches.
- **A scope decision on the U6 assessment sheet** — deliberately not built
  (BACKLOG-NEW.md NEW-8). Confirm it is Phase 2 rather than an omission.

`BACKLOG.md` holds the remaining optional items and `BACKLOG-NEW.md` the ones
found during remediation. Known and deliberately deferred: N+1 on the guardian
dashboard, `coach_no` generated as `max(id)+1`, and `venue_dv`/`venue_en`
being savable one without the other. None bites at this scale.

`AUDIT.md` and `DELTA.md` record how the original delivery failed and what
changed once SPEC.md was reconciled — useful background, not current work.
