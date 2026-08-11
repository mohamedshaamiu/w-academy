<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Routes that remain reachable while must_change_password is true.
     */
    private const ALLOWED_ROUTE_NAMES = [
        'password.change',
        'password.change.update',
        'logout',
        'locale.switch',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTE_NAMES, true)) {
            // The API has nowhere to redirect to; it gets a refusal it can act on.
            if ($request->expectsJson()) {
                abort(403, __('auth.change_password.notice'));
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
