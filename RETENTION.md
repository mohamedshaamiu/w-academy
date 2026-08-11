# Data Retention & Minor Data Protection

This document describes what personal data W-Academy's Phase 1 system stores
about minors (students), who can see it, how student logins are issued and
revoked, and how data is removed.

## What is stored about a student (minor)

| Field | Table | Notes |
|---|---|---|
| Full name | `students.full_name` | Free text, stored as typed (Thaana or Latin script) |
| Index number | `students.index_number` | Academy-issued identifier, also used as login username |
| Date of birth | `students.date_of_birth` | Used to compute age and enforce the 5–18 eligibility range |
| Gender | `students.gender` | |
| School name / class level | `students.school_name`, `students.class_level` | Optional |
| Home address | `students.address` | Free text |
| Photo | `students.photo_path` | Stored on the **private** disk (`storage/app/private`), never a public URL |
| Guardian relationships | `guardian_student` | Links to one or more guardians, with relationship type and primary/alert flags |
| Squad enrolment history | `squad_student` | Which squads, when, and current status |
| Attendance records | `attendances` | Per-session present/absent/late/excused, plus a free-text remark |
| Signed discipline agreement | `agreement_signatures` | Includes the guardian's signature (typed name and/or a drawn signature PNG), IP address, and browser user agent captured at signing time |
| Login account | `users` (linked via `students.user_id`) | Only created when an admin explicitly issues credentials — see below |

## Who can see each field

- **Admin**: full access to all student records, photos, and agreement signatures, subject to standard authentication. Every admin view of a student's detail page is written to the audit log with the causer.
- **Coach**: can see students only in squads they are assigned to as head coach, and only while that squad enrolment is active. A coach viewing their squad roster is logged.
- **Guardian**: can see only the students linked to them via `guardian_student`, enforced by `App\Policies\StudentPolicy`, not just by query scoping.
- **Student**: can see only their own record. The student portal never exposes another student's name, photo, attendance, or status — including on the schedule screen, which shows session time/venue/coach only, never a teammate list.
- Student photos are served exclusively through `GET /students/{student}/photo`, a signed, policy-checked route (`App\Http\Controllers\StudentPhotoController`) — there is no public storage URL for photos.

## Minors' own login accounts

- A student record and a student **login** are two separate things. Creating a
  student record does not create a login.
- An admin issues a student's login explicitly via the "Issue credentials"
  action (`App\Services\CredentialService::issueStudentCredentials`), which
  creates a `users` row with `username` = the student's index number and a
  randomly generated password shown to the admin exactly once on screen. The
  password is never emailed, never logged, and never stored in plain text.
- Re-issuing resets the existing login's password rather than creating a
  second account.
- An admin can revoke access at any time by setting `users.is_active = false`
  on the student's login, which immediately blocks that account from logging
  in (checked on every login attempt).
- If a student's `index_number` changes, their linked login's `username` is
  updated in the same transaction so the two never drift apart.

## Deletion and purge path

- Deleting a student record (`DELETE /admin/students/{student}`) is a
  **soft delete** (`students.deleted_at`) — the row is hidden from all normal
  queries but not physically removed, preserving referential integrity for
  historical attendance and agreement records.
- The student's linked `users` row is similarly soft-deletable independently
  (e.g. to revoke login access without losing the student record, or vice
  versa).
- **Physical (hard) purge** — for a documented right-to-erasure request or
  routine data-minimisation policy — is not automated in Phase 1. It should be
  performed manually by an administrator with direct database access, in this
  order to respect foreign keys: `attendances` → `agreement_signatures` →
  `guardian_student` → `squad_student` → `students` → (optionally) the linked
  `users` row and any stored photo/signature files under
  `storage/app/private/students/` and `storage/app/private/signatures/`. A
  scripted purge command is recommended as a Phase 2 follow-up rather than a
  raw `DELETE`, so that it can be reviewed and audited before running.
- Activity log entries (`spatie/laravel-activitylog`, table `activity_log`)
  referencing a purged student are historical audit records; whether they are
  purged alongside the student is a policy decision for the academy, not
  something this codebase decides unilaterally — the activity log is left
  intact by default.
