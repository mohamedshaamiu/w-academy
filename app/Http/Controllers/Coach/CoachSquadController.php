<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachSquadController extends Controller
{
    public function index(Request $request): View
    {
        return view('coach.squads', [
            'squads' => $request->user()->coach->squads()->withCount('activeStudents')->get(),
        ]);
    }

    public function show(Squad $squad): View
    {
        $this->authorize('view', $squad);

        activity()
            ->performedOn($squad)
            ->causedBy(request()->user())
            ->log('squad.roster_viewed');

        return view('coach.squad-detail', [
            'squad' => $squad,
            'roster' => $squad->activeStudents()->get(),
        ]);
    }
}
