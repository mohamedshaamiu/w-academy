<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Services\CredentialService;
use Illuminate\View\View;

class GuardianCredentialController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function reset(Guardian $guardian): View
    {
        $password = $this->credentials->resetGuardianCredentials($guardian);

        return view('admin.guardians.partials.credential-modal', [
            'username' => $guardian->user->username,
            'password' => $password,
        ]);
    }
}
