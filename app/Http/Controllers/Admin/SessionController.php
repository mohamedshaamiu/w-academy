<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateSessionsRequest;
use App\Http\Requests\Admin\StoreSessionRequest;
use App\Models\Squad;
use App\Models\TrainingSession;
use App\Services\SessionSchedulingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function __construct(private readonly SessionSchedulingService $scheduling) {}

    public function index(Request $request): View
    {
        return view('admin.sessions.index', [
            'sessions' => TrainingSession::query()
                ->when($request->filled('squad'), fn ($q) => $q->where('squad_id', $request->integer('squad')))
                ->when($request->filled('from'), fn ($q) => $q->whereDate('scheduled_start', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($q) => $q->whereDate('scheduled_start', '<=', $request->date('to')))
                ->with('squad', 'coach.user')
                ->orderByDesc('scheduled_start')
                ->paginate(20)
                ->withQueryString(),
            'squads' => Squad::where('is_active', true)->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sessions.create', ['squads' => Squad::where('is_active', true)->get()]);
    }

    public function store(StoreSessionRequest $request): RedirectResponse
    {
        $squad = Squad::findOrFail($request->integer('squad_id'));

        $this->scheduling->assertNoOverlap($squad, $request->date('scheduled_start'), $request->date('scheduled_end'));

        $session = TrainingSession::create([
            ...$request->validated(),
            'status' => 'planned',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.sessions.show', $session)->with('status', __('admin.session.created'));
    }

    public function show(TrainingSession $session): View
    {
        return view('admin.sessions.show', ['session' => $session->load('squad', 'coach.user', 'attendances.student')]);
    }

    public function edit(TrainingSession $session): View
    {
        return view('admin.sessions.edit', ['session' => $session, 'squads' => Squad::where('is_active', true)->get()]);
    }

    public function update(StoreSessionRequest $request, TrainingSession $session): RedirectResponse
    {
        $squad = Squad::findOrFail($request->integer('squad_id'));

        $this->scheduling->assertNoOverlap($squad, $request->date('scheduled_start'), $request->date('scheduled_end'), $session->id);

        $session->update($request->validated());

        return redirect()->route('admin.sessions.show', $session)->with('status', __('admin.session.updated'));
    }

    public function destroy(Request $request, TrainingSession $session): RedirectResponse
    {
        $reason = $request->validate(['cancellation_reason' => ['required', 'string']])['cancellation_reason'];

        $this->scheduling->cancel($session, $reason);

        return back()->with('status', __('admin.session.cancelled'));
    }

    public function generateFromSchedule(GenerateSessionsRequest $request): RedirectResponse
    {
        $squad = Squad::findOrFail($request->integer('squad_id'));

        $result = $this->scheduling->generateFromSchedule(
            $squad,
            $request->string('from')->toString(),
            $request->string('to')->toString(),
            $request->user(),
        );

        return back()->with('status', __('admin.session.generated', $result));
    }
}
