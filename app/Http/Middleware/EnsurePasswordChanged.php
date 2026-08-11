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
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
