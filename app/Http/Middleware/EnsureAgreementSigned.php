<?php

namespace App\Http\Middleware;

use App\Models\AgreementTemplate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgreementSigned
{
    /**
     * Routes that remain reachable regardless of agreement status.
     */
    private const ALLOWED_ROUTE_NAMES = [
        'agreement.show',
        'agreement.sign',
        'agreement.download',
        'guardian.profile.edit',
        'guardian.profile.update',
        'student.profile.edit',
        'student.profile.update',
        'logout',
        'locale.switch',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        $user = $request->user();

        // SPEC.md §8.3: "admins retain access so they can publish one". Admin
        // routes do not carry this middleware, but a user who holds both the
        // admin and guardian roles must not be locked out of the portal.
        if ($user->isAdmin()) {
            return $next($request);
        }

        $current = AgreementTemplate::current()->first();

        // SPEC.md §8.3: the gate fails CLOSED. With no current template there
        // is nothing to sign, so portal access is denied rather than allowed.
        if (! $current) {
            return response()->view('agreement.unavailable', [], 403);
        }

        if ($user->isGuardian()) {
            $guardian = $user->guardian;
            $unsignedStudent = $guardian->students()
                ->get()
                ->first(fn ($student) => ! $student->has_signed_current_agreement);

            if ($unsignedStudent) {
                return redirect()
                    ->route('agreement.show', $unsignedStudent)
                    ->with('notice', __('agreement.gate.guardian_must_sign'));
            }
        }

        if ($user->isStudent()) {
            $student = $user->student;

            if (! $student || ! $student->has_signed_current_agreement) {
                return response()->view('student.agreement-pending', [], 403);
            }
        }

        return $next($request);
    }
}
