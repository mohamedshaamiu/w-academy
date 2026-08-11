<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FrameworkPillar;
use App\Models\FrameworkStrikeLevel;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
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
