<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale && $request->user()) {
            $locale = $request->user()->locale?->value;
        }

        if (! in_array($locale, Locale::values(), true)) {
            $locale = Locale::Dhivehi->value;
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
