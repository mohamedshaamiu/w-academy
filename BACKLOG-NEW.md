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
