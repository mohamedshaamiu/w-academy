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
            $locale = $request->user()?->locale?->value ?? Locale::Dhivehi->value;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
