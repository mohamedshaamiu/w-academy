<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language');

        if (! in_array($locale, Locale::values(), true)) {
            // Resolve through the sanctum guard explicitly: this middleware is
            // prepended to the api group, and $request->user() would use the
            // default `web` guard, which is never populated on an API request —
            // so the user's locale column would never be consulted (SPEC.md §3.2).
            $locale = $request->user('sanctum')?->locale?->value ?? Locale::Dhivehi->value;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
