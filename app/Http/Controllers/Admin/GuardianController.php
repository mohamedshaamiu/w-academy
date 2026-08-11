<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuardianRequest;
use App\Http\Requests\Admin\UpdateGuardianRequest;
use App\Models\Guardian;
use App\Services\CredentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function __construct(private readonly CredentialService $credentials) {}

    public function index(Request $request): View
    {
        return view('admin.guardians.index', [
            'guardians' => Guardian::with('user')
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
        return view('admin.guardians.create');
    }

    public function store(StoreGuardianRequest $request): RedirectResponse
    {
        [$guardian] = $this->credentials->createOrLinkGuardian($request->validated());

        return redirect()->route('admin.guardians.show', $guardian)->with('status', __('admin.guardian.created'));
    }

    public function show(Guardian $guardian): View
    {
        return view('admin.guardians.show', ['guardian' => $guardian->load(['user', 'students'])]);
    }

    public function edit(Guardian $guardian): View
    {
        return view('admin.guardians.edit', ['guardian' => $guardian->load('user')]);
    }

    public function update(UpdateGuardianRequest $request, Guardian $guardian): RedirectResponse
    {
        $validated = $request->validated();

        $guardian->user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
        ]);

        $guardian->update([
            'address' => $validated['address'],
            'national_id' => $validated['national_id'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
        ]);

        return redirect()->route('admin.guardians.show', $guardian)->with('status', __('admin.guardian.updated'));
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $guardian->delete();

        return redirect()->route('admin.guardians.index')->with('status', __('admin.guardian.deleted'));
    }
}
