<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->isCoach() => redirect()->route('coach.dashboard'),
            $user->isGuardian() => redirect()->route('guardian.dashboard'),
            $user->isStudent() => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
