<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\CredentialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCredentialController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function issue(Request $request, Student $student): View
    {
        $password = $this->credentials->issueStudentCredentials($student, $request->user());

        return view('admin.students.partials.credential-modal', [
            'username' => $student->fresh()->user->username,
            'password' => $password,
        ]);
    }
}
