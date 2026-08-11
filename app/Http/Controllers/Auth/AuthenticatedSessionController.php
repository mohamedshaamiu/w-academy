<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($request->user()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectPathFor($user): string
    {
        if ($user->must_change_password) {
            return route('password.change');
        }

        return match (true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isCoach() => route('coach.dashboard'),
            $user->isGuardian() => route('guardian.dashboard'),
            $user->isStudent() => route('student.dashboard'),
            default => route('login'),
        };
    }
}
