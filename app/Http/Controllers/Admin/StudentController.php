<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Guardian;
use App\Models\Squad;
use App\Models\Student;
use App\Services\StudentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly StudentRegistrationService $registration) {}

    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['activeEnrolment.squad', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(function ($q) use ($term) {
                    $q->where('index_number', 'like', "%{$term}%")
                        ->orWhere('full_name', 'like', "%{$term}%")
                        ->orWhereHas('guardians.user', fn ($g) => $g->where('phone', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('squad'), fn ($q) => $q->whereHas('activeEnrolment', fn ($e) => $e->where('squad_id', $request->integer('squad'))))
            ->when($request->filled('agreement'), function ($q) use ($request) {
                $signed = $request->string('agreement')->toString() === 'signed';
                $q->whereHas('agreementSignatures', fn ($s) => $s->where('status', 'signed'), $signed ? '>=' : '<', 1);
            })
            ->when($request->filled('login'), function ($q) use ($request) {
                $request->string('login')->toString() === 'issued' ? $q->whereNotNull('user_id') : $q->whereNull('user_id');
            })
            ->latest('registered_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'squads' => Squad::where('is_active', true)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'guardians' => Guardian::with('user')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('students', 'local') : null;

        $student = $this->registration->register(
            [
                'index_number' => $request->string('index_number')->toString(),
                'full_name' => $request->string('full_name')->toString(),
                'date_of_birth' => $request->string('date_of_birth')->toString(),
                'gender' => $request->string('gender')->toString(),
                'school_name' => $request->input('school_name'),
                'class_level' => $request->input('class_level'),
                'address' => $request->string('address')->toString(),
                'photo_path' => $photoPath,
            ],
            collect($request->array('guardians'))->map(fn ($g) => $g['mode'] === 'existing'
                ? ['guardian_id' => $g['guardian_id'], 'relationship' => $g['relationship'], 'is_primary' => (bool) $g['is_primary'], 'receives_alerts' => (bool) ($g['receives_alerts'] ?? true)]
                : ['new_guardian' => $g, 'relationship' => $g['relationship'], 'is_primary' => (bool) $g['is_primary'], 'receives_alerts' => (bool) ($g['receives_alerts'] ?? true)]
            )->all(),
            $request->user(),
        );

        return redirect()->route('admin.students.show', $student)->with('status', __('admin.student.created'));
    }

    public function show(Student $student): View
    {
        activity()
            ->performedOn($student)
            ->causedBy(request()->user())
            ->log('student.viewed');

        return view('admin.students.show', [
            'student' => $student->load(['guardians.user', 'activeEnrolment.squad', 'agreementSignatures.agreementTemplate', 'user']),
        ]);
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', ['student' => $student]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        if ($photoPath = $request->hasFile('photo') ? $request->file('photo')->store('students', 'local') : null) {
            $student->photo_path = $photoPath;
        }

        $indexChanged = $student->index_number !== strtoupper(trim($request->string('index_number')->toString()));

        $student->fill([
            'full_name' => $request->string('full_name')->toString(),
            'date_of_birth' => $request->string('date_of_birth')->toString(),
            'gender' => $request->string('gender')->toString(),
            'school_name' => $request->input('school_name'),
            'class_level' => $request->input('class_level'),
            'address' => $request->string('address')->toString(),
        ])->save();

        if ($indexChanged) {
            $this->registration->updateIndexNumber($student, $request->string('index_number')->toString());
        }

        return redirect()->route('admin.students.show', $student)->with('status', __('admin.student.updated'));
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('status', __('admin.student.deleted'));
    }

    public function updateStatus(Request $request, Student $student): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:pending,active,suspended,inactive']]);

        $student->update(['status' => $request->string('status')->toString()]);

        return back()->with('status', __('admin.student.status_updated'));
    }
}
