<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Services\CredentialService;
use Illuminate\View\View;

class CoachCredentialController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function reset(Coach $coach): View
    {
        $password = $this->credentials->resetCoachCredentials($coach);

        return view('admin.coaches.partials.credential-modal', [
            'username' => $coach->user->username,
            'password' => $password,
        ]);
    }
}
