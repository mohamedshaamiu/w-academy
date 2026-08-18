<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\FrameworkPillar;
use App\Models\FrameworkStrikeLevel;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'pillars' => FrameworkPillar::active()->get(),
            'coaches' => Coach::with('user')->orderBy('coach_no')->get(),
        ]);
    }

    /**
     * SPEC.md §7's public table lists home, framework and contact. This page is
     * an addition on top of that set: static academy information only, still
     * carrying no registration or sign-up affordance (§10).
     */
    public function about(): View
    {
        return view('public.about', [
            'pillars' => FrameworkPillar::active()->get(),
        ]);
    }

    public function framework(): View
    {
        return view('public.framework', [
            'pillars' => FrameworkPillar::active()->get(),
            'strikeLevels' => FrameworkStrikeLevel::active()->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }
}
