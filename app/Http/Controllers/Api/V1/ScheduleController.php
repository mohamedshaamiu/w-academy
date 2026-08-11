<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TrainingSessionResource;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $squadIds = match (true) {
            $user->isGuardian() => $user->guardian->students()->with('activeEnrolment')->get()->pluck('activeEnrolment.squad_id')->filter(),
            $user->isStudent() => Collection::make([$user->student?->activeEnrolment?->squad_id])->filter(),
            $user->isCoach() => $user->coach->squads()->pluck('id'),
            default => Collection::make(),
        };

        $sessions = TrainingSession::whereIn('squad_id', $squadIds)
            ->where('scheduled_start', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_start')
            ->with('squad', 'coach.user')
            ->get();

        return TrainingSessionResource::collection($sessions);
    }
}
