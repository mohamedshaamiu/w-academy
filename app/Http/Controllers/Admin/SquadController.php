<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSquadRequest;
use App\Http\Requests\Admin\UpdateSquadRequest;
use App\Models\Coach;
use App\Models\Squad;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SquadController extends Controller
{
    public function index(): View
    {
        return view('admin.squads.index', [
            'squads' => Squad::with('headCoach.user')->withCount('activeStudents')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.squads.create', ['coaches' => Coach::with('user')->get()]);
    }

    public function store(StoreSquadRequest $request): RedirectResponse
    {
        $squad = Squad::create($request->validated());

        return redirect()->route('admin.squads.show', $squad)->with('status', __('admin.squad.created'));
    }

    public function show(Squad $squad): View
    {
        return view('admin.squads.show', [
            'squad' => $squad->load('headCoach.user'),
            'roster' => $squad->activeStudents()->get(),
        ]);
    }

    public function edit(Squad $squad): View
    {
        return view('admin.squads.edit', ['squad' => $squad, 'coaches' => Coach::with('user')->get()]);
    }

    public function update(UpdateSquadRequest $request, Squad $squad): RedirectResponse
    {
        $squad->update($request->validated());

        return redirect()->route('admin.squads.show', $squad)->with('status', __('admin.squad.updated'));
    }

    public function destroy(Squad $squad): RedirectResponse
    {
        $squad->delete();

        return redirect()->route('admin.squads.index')->with('status', __('admin.squad.deleted'));
    }
}
