<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\CredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentGuardianController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function store(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:existing,new'],
            'guardian_id' => ['required_if:mode,existing', 'nullable', 'integer', 'exists:guardians,id'],
            'name' => ['required_if:mode,new', 'nullable', 'string', 'max:150'],
            'phone' => ['required_if:mode,new', 'nullable', 'regex:/^[79]\d{6}$/'],
            'address' => ['required_if:mode,new', 'nullable', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:50'],
            'is_primary' => ['boolean'],
            'receives_alerts' => ['boolean'],
        ]);

        $guardian = $validated['mode'] === 'existing'
            ? Guardian::findOrFail($validated['guardian_id'])
            : $this->credentials->createOrLinkGuardian($validated)[0];

        if (! empty($validated['is_primary'])) {
            $student->guardians()->updateExistingPivot(
                $student->guardians()->pluck('guardians.id')->all(),
                ['is_primary' => false]
            );
        }

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'relationship' => $validated['relationship'],
                'is_primary' => (bool) ($validated['is_primary'] ?? false),
                'receives_alerts' => (bool) ($validated['receives_alerts'] ?? true),
            ],
        ]);

        return back()->with('status', __('admin.student.guardian_linked'));
    }

    public function destroy(Student $student, Guardian $guardian): RedirectResponse
    {
        if ($student->guardians()->count() <= 1) {
            return back()->withErrors(['guardian' => __('admin.student.validation.at_least_one_guardian_remains')]);
        }

        $student->guardians()->detach($guardian->id);

        return back()->with('status', __('admin.student.guardian_unlinked'));
    }
}
