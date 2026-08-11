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
