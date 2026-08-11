<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCoachRequest;
use App\Http\Requests\Admin\UpdateCoachRequest;
use App\Models\Coach;
use App\Services\CredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function index(Request $request): View
    {
        return view('admin.coaches.index', [
            'coaches' => Coach::with('user')
                ->when($request->filled('q'), function ($q) use ($request) {
                    $term = $request->string('q')->toString();
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
                })
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.coaches.create');
    }

    public function store(StoreCoachRequest $request): RedirectResponse
    {
        [$coach] = $this->credentials->createCoach($request->validated());

        return redirect()->route('admin.coaches.show', $coach)->with('status', __('admin.coach.created'));
    }

    public function show(Coach $coach): View
    {
        return view('admin.coaches.show', ['coach' => $coach->load(['user', 'squads'])]);
    }

    public function edit(Coach $coach): View
    {
        return view('admin.coaches.edit', ['coach' => $coach->load('user')]);
    }

    public function update(UpdateCoachRequest $request, Coach $coach): RedirectResponse
    {
        $validated = $request->validated();

        $coach->user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
        ]);

        $coach->update([
            'specialisation' => $validated['specialisation'] ?? null,
            'joined_on' => $validated['joined_on'],
        ]);

        return redirect()->route('admin.coaches.show', $coach)->with('status', __('admin.coach.updated'));
    }

    public function destroy(Coach $coach): RedirectResponse
    {
        $coach->delete();

        return redirect()->route('admin.coaches.index')->with('status', __('admin.coach.deleted'));
    }
}
