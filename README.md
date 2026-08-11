# W-Academy Management System — Phase 1

A bilingual (Dhivehi / English) Laravel web application for W-Academy, a youth
football academy in the Maldives. Phase 1 delivers: role-based auth for
Admin / Coach / Guardian / Student; admin-only creation of students,
guardians and coaches; a mandatory digital discipline agreement signed by the
guardian; squad enrolment; training session scheduling; attendance marking;
and a read-only Framework Reference Guide. See the project brief for the full
Phase 1 scope and the out-of-scope list for later phases.

## Tech stack

PHP 8.2+, Laravel 11, MySQL 8, Blade + Tailwind CSS (Vite), Alpine.js,
spatie/laravel-permission, spatie/laravel-activitylog, intervention/image,
Laravel Breeze (Blade stack, heavily customised — registration and
self-service password reset were removed entirely).

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL 8 database matching `.env` (`DB_DATABASE=wacademy` by
default), then:

```bash
php artisan migrate
php artisan db:seed
npm install
npm run build      # or `npm run dev` while developing
php artisan serve
```

`php artisan migrate:fresh --seed` also works for a clean rebuild.

### Seeding

`database/seeders/DatabaseSeeder.php` always runs:
`RolePermissionSeeder`, `FrameworkPillarSeeder`, `StrikeLevelSeeder`,
`AgreementTemplateSeeder`, `AdminUserSeeder`.

`DemoDataSeeder` (realistic-shaped Maldivian demo coaches/guardians/students/
squads/sessions) only runs when `APP_ENV=local`, so production seeding never
includes demo data.

### Default admin login

- Username: `7900000`
- Password: `WAcademy@2026`
- `must_change_password` is true, so the very first login forces a password
  change before anything else is reachable.

## The admin workflow: adding a student and issuing their login

1. Log in as an admin and go to **Students → Add Student**
   (`/admin/students/create`).
2. Fill in the student's index number, name, date of birth (age must be
   5–18), gender and address. Thaana and Latin script are both accepted in
   every free-text field.
3. Link at least one guardian — either search/select an existing guardian or
   create one inline. Exactly one linked guardian must be marked "primary".
4. Save. The student is created with `status = pending` and **no login yet**
   — creating the record never auto-creates credentials.
5. Open the student's detail page and click **Issue login** in the
   Credentials panel. A random 8-character password is generated and shown
   **once** in a modal with a copy button — hand it to the family on paper.
   Re-clicking later (now labelled "Reset password") rotates the password
   without creating a second account.
6. The student becomes fully `active` automatically once their guardian signs
   the current discipline agreement **and** they hold an active squad
   enrolment (`app/Services/AgreementService::maybeActivateStudent`).

Guardians and coaches follow the same "create → auto-generated password shown
once" pattern from their respective admin index pages.

## Adding or changing a translation key

- Interface strings live in `lang/en/<domain>.php` and `lang/dv/<domain>.php`,
  split by domain (`common`, `auth`, `validation`, `nav`, `agreement`,
  `student`, `guardian`, `coach`, `squad`, `session`, `attendance`,
  `framework`, `admin`, `report`, `public`).
- **Both files must have identical key structures** — this is enforced by
  `LocalisationTest::test_lang_dv_and_en_have_identical_key_sets`, which
  recursively diffs every domain file. Adding a key to only one language
  breaks the build.
- Never hardcode user-facing text in a Blade view, controller, enum, or
  validation rule — always go through `__('domain.key')` or `@lang`. A second
  test, `test_no_untranslated_literals_in_blade_views`, heuristically scans
  `resources/views` for capitalised English text sitting outside `__()`/
  `@lang` and will flag likely violations (it's a heuristic, not a full
  parser — review any hit on its merits).
- Enum labels (`App\Enums\*`) call `__('domain.status.'.$this->value)` via a
  `label()` method — never echo the raw enum value in a view.
- `*_dv` / `*_en` **content** columns (squad names, session venues, framework
  pillar text, the agreement body, etc.) are a different mechanism — see
  `App\Concerns\HasTranslatedAttributes::translated()`. Both language columns
  are required on the admin form for that content; there's no single-language
  content column anywhere.

## Changing the strike ladder

The discipline strike ladder is entirely table-driven
(`framework_strike_levels`), edited at **Admin → Framework → Strike Levels**.
No controller, service, or Blade view hardcodes the number of levels, timeout
durations, or which level triggers suspension — the trigger level is read
from `config('academy.suspension_trigger_level')`, which reads
`ACADEMY_SUSPENSION_TRIGGER_LEVEL` from `.env` (default `3`). Changing that
env value changes which strike level is treated as the suspension trigger
without touching any code; the actual per-level content (label, type, action,
parent's role, timeout minutes, and which trigger flags are set) is edited
per-row through the admin UI.

## Deployment (CyberPanel / OpenLiteSpeed, Git + SSH)

1. On the VPS, create a website in CyberPanel and note its document root.
2. `git clone` (or `git pull` on redeploy) the repository into that document
   root, or into a sibling directory with the document root symlinked to
   `public/` — either is fine as long as only `public/` is web-exposed.
3. `composer install --no-dev --optimize-autoloader`
4. Copy `.env.example` to `.env`, fill in production `DB_*`, `APP_URL`,
   `APP_KEY` (via `php artisan key:generate` if not already set), and a real
   `MAIL_*` config.
5. `php artisan migrate --force`
6. `php artisan db:seed --force` (this seeds roles/permissions, the
   framework reference content, and the admin account — **not** demo data,
   since `APP_ENV` will be `production`).
7. `npm install && npm run build` (or build locally and deploy the
   `public/build` directory if Node isn't available on the VPS).
8. `php artisan storage:link` — only needed if the app ever serves anything
   from the `public` disk; student photos and signatures stay on the
   `local` (private) disk and are never linked into `public/`.
9. Point OpenLiteSpeed's document root at `public/`, ensure `storage/` and
   `bootstrap/cache/` are writable by the web server user, and set
   `APP_ENV=production` / `APP_DEBUG=false` in `.env`.
10. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
    after every deploy.
11. Re-run steps 3–10 (skip the seed step, or make it idempotent-safe) on
    every subsequent `git pull` deploy.

## Testing

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in
`phpunit.xml`), independent of the MySQL connection used for local
development — no extra setup needed to run the suite.

## Packages beyond the required stack

None — `laravel/sanctum` was added for the `/api/v1` bearer-token
authentication the brief explicitly asks for ("API (prefix /api/v1, sanctum)"
in the routes section), which wasn't listed in the tech-stack section but is
required to implement it.

See the deviation notes at the end of the project handoff for anything
implemented differently than literally specified, and why.
