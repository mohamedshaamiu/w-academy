<?php

namespace Tests\Feature;

use App\Models\AgreementTemplate;
use App\Models\Coach;
use App\Models\FrameworkPillar;
use App\Models\FrameworkStrikeLevel;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\User;
use Database\Seeders\FrameworkPillarSeeder;
use Database\Seeders\StrikeLevelSeeder;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The safety net for every later work package.
 *
 * Walks the whole route table and proves, for every non-public route, that a
 * guest is turned away and that a user holding the wrong role is refused.
 * A route that fits none of the categories below fails the test rather than
 * being silently skipped, so a newly added route cannot escape coverage.
 */
class RouteCoverageTest extends TestCase
{
    /**
     * Routes SPEC.md §7 lists as public, plus framework infrastructure.
     *
     * @var array<int, string>
     */
    private const PUBLIC_ROUTE_NAMES = [
        // SPEC.md §7 "Public (no auth)"
        'home',
        'framework',
        'contact',
        'locale.switch',
        // SPEC.md §7 "Auth" — the login screen must be reachable by a guest
        'login',
        // SPEC.md §7 API — the token endpoint is necessarily unauthenticated
        'api.v1.auth.login',
    ];

    /**
     * Framework-registered routes outside SPEC.md §7's table.
     * `storage.local` is tracked as BACKLOG-NEW.md NEW-1 (P2).
     *
     * @var array<int, string>
     */
    private const INFRASTRUCTURE_ROUTE_NAMES = [
        'sanctum.csrf-cookie',
        'storage.local',
    ];

    /**
     * Authenticated routes that legitimately carry no role middleware, because
     * every authenticated role may reach them. Each still gets the guest check.
     *
     * @var array<string, string>
     */
    private const ROLE_AGNOSTIC_ROUTES = [
        'dashboard.redirect' => 'Dispatches each role to its own dashboard.',
        'password.change' => 'SPEC.md §8.1 — reachable by every role on first login.',
        'password.change.update' => 'SPEC.md §8.1 — reachable by every role on first login.',
        'logout' => 'Every authenticated role may log out.',
        'students.photo' => 'SPEC.md §8.6 — guarded by a signed URL plus StudentPolicy@viewPhoto, not by role.',
        'api.v1.auth.logout' => 'SPEC.md §7 — any authenticated caller may revoke their own token.',
        'api.v1.me' => 'SPEC.md §7 — returns the caller\'s own identity, whatever their role.',
        'api.v1.schedule' => 'SPEC.md §7 — "scoped to the caller\'s role", so every role is admissible.',
        'api.v1.framework' => 'SPEC.md §7 — framework reference is readable by every role (§6 grants framework.view to all).',
    ];

    /**
     * Routes whose role restriction lives in the controller rather than in
     * route middleware. They still owe a 403 to the wrong role.
     *
     * @var array<string, string>
     */
    private const CONTROLLER_ENFORCED_ROLES = [
        // SPEC.md §7: "guardian only: students + agreement status".
        // Enforced at Api/V1/ChildrenController.php:15 via abort_unless().
        'api.v1.children' => 'guardian',
    ];

    private array $bindings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FrameworkPillarSeeder::class);
        $this->seed(StrikeLevelSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $student = Student::factory()->create(['created_by' => $admin->id]);
        $guardian = Guardian::factory()->create();
        $coach = Coach::factory()->create();
        $squad = Squad::factory()->create(['head_coach_id' => $coach->id]);

        $this->bindings = [
            'student' => $student->getKey(),
            'guardian' => $guardian->getKey(),
            'coach' => $coach->getKey(),
            'squad' => $squad->getKey(),
            'session' => TrainingSession::factory()->create([
                'squad_id' => $squad->id,
                'coach_id' => $coach->id,
                'created_by' => $admin->id,
            ])->getKey(),
            'agreement_template' => AgreementTemplate::factory()->create()->getKey(),
            'pillar' => FrameworkPillar::query()->value('id'),
            'strike_level' => FrameworkStrikeLevel::query()->value('id'),
            'locale' => 'en',
            'path' => 'nothing-here.txt',
        ];
    }

    public function test_every_non_public_route_rejects_guests_and_wrong_roles(): void
    {
        $guestFailures = [];
        $roleFailures = [];
        $uncovered = [];
        $guarded = [];

        // --- Pass 1: guests. Runs before any authentication so a leaked guard
        // --- cannot make a protected route look reachable (or unreachable).
        foreach ($this->auditableRoutes() as $route) {
            $name = $route->getName();
            $middleware = $route->gatherMiddleware();

            if (in_array($name, self::PUBLIC_ROUTE_NAMES, true)
                || in_array($name, self::INFRASTRUCTURE_ROUTE_NAMES, true)) {
                continue;
            }

            $isApi = in_array('auth:sanctum', $middleware, true);

            if (! $isApi && ! in_array('auth', $middleware, true)) {
                $uncovered[] = "{$name} — no auth middleware and not on the public allowlist";

                continue;
            }

            $guarded[] = [$route, $isApi];

            $response = $this->requestAsGuest($route, $isApi);

            if ($isApi) {
                if ($response->getStatusCode() !== 401) {
                    $guestFailures[] = "{$name} — expected 401 for a guest, got {$response->getStatusCode()}";
                }
            } elseif ($response->getStatusCode() !== 302
                || ! str_contains((string) $response->headers->get('Location'), '/login')) {
                $guestFailures[] = "{$name} — expected a 302 redirect to /login for a guest, got "
                    .$response->getStatusCode().' → '.($response->headers->get('Location') ?? 'no location');
            }
        }

        // --- Pass 2: wrong-role users.
        foreach ($guarded as [$route, $isApi]) {
            $name = $route->getName();

            $requiredRole = $this->requiredRole($route->gatherMiddleware())
                ?? (self::CONTROLLER_ENFORCED_ROLES[$name] ?? null);

            if ($requiredRole === null) {
                if (! array_key_exists($name, self::ROLE_AGNOSTIC_ROUTES)) {
                    $uncovered[] = "{$name} — authenticated but has no role middleware and no documented reason";
                }

                continue;
            }

            $wrongRole = $this->wrongRoleFor($requiredRole);
            $response = $this->requestAsUser($route, $this->userWithRole($wrongRole), $isApi);

            if ($response->getStatusCode() !== 403) {
                $roleFailures[] = "{$name} — expected 403 for a '{$wrongRole}' user on a 'role:{$requiredRole}' route, got "
                    .$response->getStatusCode();
            }
        }

        $this->assertSame([], $uncovered, "Routes escaping coverage:\n".implode("\n", $uncovered));
        $this->assertSame([], $guestFailures, "Routes reachable by a guest:\n".implode("\n", $guestFailures));
        $this->assertSame([], $roleFailures, "Routes reachable by the wrong role:\n".implode("\n", $roleFailures));
    }

    public function test_route_table_contains_no_registration_or_password_reset_route(): void
    {
        $forbidden = ['register', 'password.request', 'password.email', 'password.reset', 'password.store', 'verification.notice'];

        $found = collect(Route::getRoutes())
            ->map(fn (RoutingRoute $route) => $route->getName())
            ->filter(fn (?string $name) => $name !== null && in_array($name, $forbidden, true))
            ->values()
            ->all();

        $this->assertSame([], $found, 'SPEC.md §12 forbids these routes: '.implode(', ', $found));
    }

    public function test_no_undeclared_public_route_serves_the_private_disk(): void
    {
        // BACKLOG-NEW.md NEW-1 — currently refused by the framework's private
        // visibility check, but the route carries no middleware of its own.
        $this->markTestIncomplete(
            'Deferred as BACKLOG-NEW.md NEW-1 (P2): storage/{path} is registered with no middleware. '
            .'Verified not exploitable today (403 unsigned and signed).'
        );
    }

    /**
     * @return array<int, RoutingRoute>
     */
    private function auditableRoutes(): array
    {
        return collect(Route::getRoutes())
            ->filter(fn (RoutingRoute $route) => $route->getName() !== null)
            ->filter(fn (RoutingRoute $route) => ! str_starts_with($route->uri(), '_'))
            ->values()
            ->all();
    }

    private function requestAsGuest(RoutingRoute $route, bool $isApi)
    {
        // actingAs() mutates the shared guard for the rest of the test, so a
        // guest pass must explicitly clear it rather than assume it is clean.
        $this->app['auth']->forgetGuards();

        return $this->dispatch($route, $isApi);
    }

    private function requestAsUser(RoutingRoute $route, User $user, bool $isApi)
    {
        $this->app['auth']->forgetGuards();
        $this->actingAs($user, $isApi ? 'sanctum' : 'web');

        return $this->dispatch($route, $isApi);
    }

    private function dispatch(RoutingRoute $route, bool $isApi)
    {
        $method = collect($route->methods())->first(fn (string $m) => $m !== 'HEAD') ?? 'GET';
        $uri = $this->resolveUri($route);

        $response = match (true) {
            $isApi && $method === 'GET' => $this->getJson($uri),
            $isApi => $this->json($method, $uri),
            $method === 'GET' => $this->get($uri),
            default => $this->call($method, $uri),
        };

        return $response->baseResponse;
    }

    private function resolveUri(RoutingRoute $route): string
    {
        $uri = $route->uri();

        foreach ($this->bindings as $param => $value) {
            $uri = str_replace(['{'.$param.'}', '{'.$param.'?}'], (string) $value, $uri);
        }

        // Any parameter this test does not know about must not silently become
        // a literal brace in the URL — surface it instead.
        $this->assertDoesNotMatchRegularExpression(
            '/\{[a-z_]+\??\}/',
            $uri,
            "RouteCoverageTest has no binding for a parameter in '{$route->getName()}' ({$route->uri()}). Add one to \$bindings."
        );

        return '/'.ltrim($uri, '/');
    }

    private function requiredRole(array $middleware): ?string
    {
        foreach ($middleware as $entry) {
            if (preg_match('/(?:^role|RoleMiddleware):(.+)$/', $entry, $matches)) {
                return explode('|', $matches[1])[0];
            }
        }

        return null;
    }

    private function wrongRoleFor(string $role): string
    {
        return collect(['student', 'coach', 'guardian', 'admin'])
            ->reject(fn (string $candidate) => $candidate === $role)
            ->first();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
