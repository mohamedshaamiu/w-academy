# BACKLOG-NEW — defects found during Stage 2, deferred out of the current work package

Per standing rule 3: found outside the active package, recorded with severity and evidence, not fixed.

---

## NEW-1 — `storage/{path}` is an undeclared, middleware-free route over the private disk

- **Severity:** **P2** (defence in depth + route-table conformance). **Not P0 — verified not exploitable.**
- **Found during:** WP-1, while enumerating non-public routes for `RouteCoverageTest`.
- **SPEC ref:** §7 (route table is exhaustive), §8.6 (student media served *only* through a signed, **policy-checked** route), §13 DoD item 5 (*"`route:list` shows no route missing its intended middleware"*).

**What it is.** `config/filesystems.php:33-39` sets `'serve' => true` on the `local` disk, whose root is `storage_path('app/private')`. Laravel 11 responds by auto-registering:

```
URI: storage/{path}   NAME: storage.local   MIDDLEWARE: (none)   ACTION: Closure
```

That path root is exactly where student media is written — `StudentController.php:60,102` → `store('students','local')` and `AgreementController.php:75-76` → `signatures/{student_id}/*.png`.

**Why it is not a P0.** I planted probe files at `students/` and `signatures/999/` on the `local` disk and drove real requests through the HTTP kernel:

| Request | Status |
|---|---|
| `GET /storage/students/AUDIT-PROBE.txt` (unauthenticated) | **403** |
| `GET /storage/signatures/999/AUDIT-PROBE.txt` (unauthenticated) | **403** |
| `GET /storage/students/AUDIT-PROBE.txt` with a valid `URL::signedRoute()` signature | **403** |

The framework's serve handler refuses files whose disk visibility is private, so no minor's photo or signature is reachable. Probe files were deleted in the same command. **No child data is exposed today.**

**Why it still matters.**
1. The route is absent from SPEC.md §7's route table, which §7 presents as the complete set.
2. It carries **no middleware at all**, so §13 DoD item 5 cannot be satisfied as written.
3. It is a second delivery path for private media whose only protection is a framework-internal visibility check — not `StudentPolicy@viewPhoto`. §8.6 requires policy checking, and a future change to disk visibility, an upgrade to the serve handler, or a switch to a disk with `'visibility' => 'public'` would silently open it.

**Recommended fix (not applied):** set `'serve' => false` on the `local` disk — the app never uses this route; `StudentPhotoController` streams via `Storage::disk('local')->response()`. Confirm no regression in signature rendering (`guardian/agreement-download.blade.php:24-25` reads bytes directly, not over HTTP).

**Files affected:** `config/filesystems.php:33-39`, `.env.example`.
**Proving test:** `RouteCoverageTest::test_no_undeclared_public_route_serves_the_private_disk`.
**Depends on:** interacts with P0-1 and P3-01 (private-disk naming) — sequence after P0-1.

**Interim handling in WP-1:** `RouteCoverageTest` allowlists `storage.local` as framework infrastructure with this ID cited inline, so the route is accounted for rather than silently skipped.

---

## NEW-2 — `agreement.unavailable` ships with placeholder Dhivehi copy

- **Severity:** **P2** (content, blocks go-live for the `dv` audience — not acceptance of the mechanism).
- **Found during:** WP-2, P0-4.
- **SPEC ref:** §3.6 (seed `[DV CONTENT PENDING]` rather than fabricate), §8.3 (the fail-closed screen shows "a translated message directing them to the academy office").

Making the gate fail closed required a screen that did not exist, and therefore two new lang keys. English is written; **Dhivehi is seeded as `[DV CONTENT PENDING]`** because §3.6 forbids fabricating or machine-translating Dhivehi and none was supplied:

```php
// lang/dv/agreement.php
'unavailable' => [
    'title' => '[DV CONTENT PENDING]',
    'body'  => '[DV CONTENT PENDING]',
],
```

The mechanism is complete and tested (`AgreementGateTest::test_gate_denies_when_no_current_template_exists`); only the copy is outstanding. A Dhivehi-speaking guardian hitting this screen today sees the literal placeholder.

**English source to translate:**
- title — "Agreement unavailable"
- body — "No discipline agreement is currently published, so the portal cannot be opened yet. Please contact the academy office."

**Files affected:** `lang/dv/agreement.php`, `resources/views/agreement/unavailable.blade.php` (no change needed).
**Proving test:** `LocalisationTest::test_no_placeholder_content_in_dv_lang_files` (BACKLOG.md P1-11 — note that item now covers only genuinely-missing copy, since `public.contact.*` placeholders were confirmed correct in DELTA.md).
**Depends on:** customer-supplied Dhivehi copy.

---

## NEW-3 — `AgreementSignature` docblock references a factory that does not exist

- **Severity:** **P3** (static-analysis noise; no runtime impact).
- **Found during:** WP-2, P0-3.
- **SPEC ref:** §11 (`database/factories/...`).

`app/Models/AgreementSignature.php:16` carries `/** @use HasFactory<AgreementSignatureFactory> */` and imports `Database\Factories\AgreementSignatureFactory`, but no such class exists — `database/factories/` holds only 8 factories and this is not among them. Calling `AgreementSignature::factory()` would fail at runtime; nothing currently does, because the model is only ever created through `AgreementService::sign()`.

Pre-existing, not introduced by WP-2, and left untouched under standing rule 3.

**Recommended fix (not applied):** add `database/factories/AgreementSignatureFactory.php`, or drop the unused import and the generic docblock parameter.
**Files affected:** `app/Models/AgreementSignature.php:7,16`.
**Proving test:** `Unit/AgreementSignatureFactoryTest::test_factory_creates_a_valid_signature`.

---

## NEW-4 — The attendance report's "Present" column counts present **and** late

- **Severity:** **P3** (labelling accuracy).
- **Found during:** WP-3, P1-A.
- **SPEC ref:** §8.7 (config-driven `late` treatment), §9.28 (attendance report).

The report's numerator column is headed `report.csv.present` — "Present" — but
with the academy's confirmed policy that `late` counts as attended, it now
tallies `present + late`. The number is correct; the heading understates what
it contains. This was equally true before WP-3 (the old inline code also
counted `['present','late']`); consolidating into `AttendanceStatisticsService`
made it visible rather than introducing it.

Not fixed here because a truthful heading — "Attended" — needs a new lang key
in both languages, and the Dhivehi copy would have to be invented (§3.6).
The underlying array key was renamed `present` → `attended` in WP-3, so only
the user-facing label is out of step.

**Recommended fix (not applied):** add `report.csv.attended` in `lang/en` and
`lang/dv`, point the table header and CSV header at it, and retire
`report.csv.present` if nothing else uses it.
**Files affected:** `lang/{en,dv}/report.php`, `resources/views/admin/reports/attendance.blade.php:39`, `app/Http/Controllers/Admin/ReportController.php:52`.
**Proving test:** `AttendanceStatisticsTest::test_report_column_heading_matches_the_configured_policy`.
**Depends on:** Dhivehi copy for "Attended".

---

## NEW-5 — The public site ships with authored ("demo") copy, including Dhivehi

- **Severity:** **P1** (content governance — blocks go-live, not acceptance of
  the mechanism).
- **Found during:** the public-site build (slider, vision/mission, about page).
- **SPEC ref:** §3.6 (*"Do NOT fabricate, machine-translate, or invent Dhivehi
  or English content"*), §12 (*"No placeholder or lorem ipsum content"*).

**What it is.** The public pages were rebuilt to be informative: a rotating
hero, an academy intro, vision and mission, values, the four pillars, a
"how to join" sequence, an enriched contact block and a new `/about` page.
None of that copy was supplied by W-Academy. It was **authored** — in **both**
English and Dhivehi — on the customer's instruction, so the demo reads as a
finished site rather than a page of `[CONTENT PENDING]` markers.

This is a deliberate, recorded deviation from §3.6, not an oversight.

**Where it is.** Every authored string carries the marker
`DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)` immediately above it:

```
lang/en/public.php        hero, intro, vision, mission, values, pillars,
lang/dv/public.php        how, cta, about.*, contact.lead, footer.*
lang/en/framework.php     page.lead, page.how_to_read_*
lang/dv/framework.php     page.lead, page.how_to_read_*
database/seeders/FrameworkPillarSeeder.php   description_dv / description_en
```

Find them all with:

```bash
grep -rn "DEMO COPY" lang/ database/seeders/
```

**What is NOT authored.** The pillar *names* (home, school, religion, sport)
are stated as fact in SPEC.md §1. `public.contact.address_value` and
`phone_value` remain `[DV/EN CONTENT PENDING]` — those are academy facts
nobody can invent. `StrikeLevelSeeder` was left entirely untouched: the strike
ladder's type/action/parent-role copy is genuine academy policy and §14 still
lists levels 4 and 5 as undefined.

**Recommended handling.** Walk the marked keys with the customer and either
confirm the wording or replace it. To return any of them to the strict §3.6
position, substitute `[DV CONTENT PENDING]` / `[EN CONTENT PENDING]` — the
templates already render the "not available in this language" hint through
`HasTranslatedAttributes::isTranslationFallback()` and need no change.
`FrameworkPillarSeeder` is idempotent on `code`, so re-running it applies
whatever the file says.

**Files affected:** `lang/{en,dv}/public.php`, `lang/{en,dv}/framework.php`,
`database/seeders/FrameworkPillarSeeder.php`.
**Proving test:** none — this is a content decision, not a defect. Coverage of
the *mechanism* is `PublicPagesTest`.
**Depends on:** the customer.

---

## NEW-6 — `/about` is a public route outside SPEC.md §7's table

- **Severity:** **P3** (route-table conformance).
- **Found during:** the public-site build.
- **SPEC ref:** §7 (the public route table), §10 (out of scope).

`GET /about` → `Public\PublicController@about` was added so the academy story,
vision, mission, values and portal overview have somewhere to live that is not
the home page. §7 lists only `/`, `/locale/{locale}`, `/framework` and
`/contact` as public.

It is static information only. It contains no registration or sign-up
affordance, adds no migration, model or write path, and touches nothing in
§10's do-not-build list — the same shape as `/contact`, which §7 does allow.
`RouteCoverageTest::PUBLIC_ROUTE_NAMES` allowlists it with that reasoning
inline, and `PublicPagesTest::test_public_pages_offer_no_registration_or_password_reset_link`
asserts the no-sign-up property against the rendered HTML.

**Recommended handling.** Fold `/about` into SPEC.md §7's public table at the
next spec revision, or drop the page and move its sections onto `/`.
**Files affected:** `routes/web.php`, `app/Http/Controllers/Public/PublicController.php`, `tests/Feature/RouteCoverageTest.php`.

---

## NEW-7 — Customer content document received; Dhivehi seeded, English still owed

- **Severity:** content decision (not a defect).
- **Found during:** content integration, 18 Aug 2026.
- **SPEC ref:** §3.6 (source content), §14 (customer-owed items).

The customer supplied `customer documents/W CDMY.pdf` — the code of conduct,
the three-strike discipline ladder and the parental agreement form, all in
Dhivehi — plus the academy crest (`logo.jpeg`). The PDF embeds every Thaana
run as an image with a corrupted text layer, so the Dhivehi was transcribed by
hand, cross-checked against the character counts recoverable from the broken
text layer, and verified by re-rendering in MV Boli against the original.
`customer documents/TRANSCRIPTION-DV.md` records the transcription and marks
the handful of words (`؟`) that still deserve a native speaker's proofread.

Seeded from it: `AgreementTemplateSeeder` (real title, body and the
discipline-acknowledgement clause label), `StrikeLevelSeeder` (all three
strike levels, with escalation flags matching the document: 3–5 min time-out
at strike 1, meeting/call at strike 2, suspension at strike 3),
`FrameworkPillarSeeder` (the four pillar descriptions, dv side).

**Still owed by the customer:**
1. **English translations** for all of the above — §3.6 forbids
   machine-translating, so every `*_en` column stays `[EN CONTENT PENDING]`.
2. **A Dhivehi proofread** of the `؟`-flagged words in TRANSCRIPTION-DV.md.
3. The **photo-consent clause** text (not present in the document, both
   languages pending) — note the agreement currently signs with a pending
   photo-consent label.
4. The pillar descriptions' **English** side is still the pre-document demo
   copy (NEW-5) — close in spirit but not a translation of the now-real
   Dhivehi; confirm or replace.

---

## NEW-8 — U6 Physical Check-Up & Assessment Sheet: out of Phase-1 scope

- **Severity:** scope note.
- **SPEC ref:** §10 (behaviour check-ins and pillar *ratings* are excluded;
  assessments appear nowhere in SPEC.md).

The customer also supplied `customer documents/U6 Physical Check-Up Chart &
Assessment Sheet.pdf` (English): an Under-6 fundamental-movement and health
assessment form — vitals, motor-skill tests with D/C/A rubric, coach summary,
parent signature. Building it would need new migrations, models, routes and
UI with no SPEC coverage, closest in spirit to §10's excluded check-ins and
report-card uploads. Recorded here as a Phase-2 candidate; nothing built.

---

## NEW-9 — Homepage coach section + public signed coach-photo route

- **Severity:** P3 (route-table conformance, same shape as NEW-6).
- **Found during:** customer request, 18 Aug 2026 ("at the homepage they need
  coach photos and details added").

The homepage now renders the academy's coaches (name, specialisation, joined
year, optional photo) from the coach records the admin maintains. Photos are
stored on the private disk and served by `GET /coaches/{coach}/photo`
(`coaches.photo`), which carries only the `signed` middleware: the section is
public by design, coaches are staff rather than child data, so §8.6's
auth+policy requirement does not apply — the signature only prevents URL
enumeration. Admin coach create/edit forms accept the upload. Proving test:
`CoachPhotoTest`; the route is allowlisted with reasoning in
`RouteCoverageTest::PUBLIC_ROUTE_NAMES`. Fold into SPEC.md §7 at the next
revision.

---

## NEW-10 — Academy photography on the public site

- **Severity:** closed for these seven photographs; one related item stays open
  under NEW-7.
- **Found during:** customer request, 18 Aug 2026 (seven photographs supplied
  in `client photos/`).

The public site is now photographic: the hero slides, the home intro, the
vision & mission band, "how to join", the closing CTA, the `/about`,
`/framework` and `/contact` heroes, an `/about` gallery band, the login screen
and the portal top bar all render the academy's own photos through
`<x-site-photo>` (CLAUDE.md § Photography, `SitePhotoTest`).

Every photograph shows identifiable children, so publication was raised before
going ahead. **Confirmed 19 Aug 2026: these seven are approved.** That approval
is specific to them — it is not a standing licence for photographs added later,
and a new photograph needs the same check.

The originals are gitignored at the customer's request, so regenerating the
renditions means asking the customer for the source files again.

Still open, tracked under NEW-7 item 3: the agreement's **photo-consent clause
itself**, `[CONTENT PENDING]` in both languages. Until it exists, consent for
each intake rests on a side conversation rather than on the document guardians
sign.
