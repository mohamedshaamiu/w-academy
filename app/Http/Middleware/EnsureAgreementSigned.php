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
        $current = AgreementTemplate::current()->first();

        if (! $current) {
            return $next($request);
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
