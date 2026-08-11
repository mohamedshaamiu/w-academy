<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyStudentSessionLifetime
{
    /**
     * Students get a shorter inactivity timeout than other roles. Must run
     * BEFORE Laravel's StartSession middleware (which reads session.lifetime
     * when it boots the session) so it needs to be prepended to the web
     * group, not appended.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('student', 'student/*')) {
            config(['session.lifetime' => (int) config('academy.student_session_lifetime')]);
        }

        return $next($request);
    }
}
