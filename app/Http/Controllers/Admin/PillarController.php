<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrameworkPillar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PillarController extends Controller
{
    public function index(): View
    {
        return view('admin.framework.pillars', ['pillars' => FrameworkPillar::orderBy('sort_order')->get()]);
    }

    public function edit(FrameworkPillar $pillar): View
    {
        return view('admin.framework.pillar-edit', ['pillar' => $pillar]);
    }

    public function update(Request $request, FrameworkPillar $pillar): RedirectResponse
    {
        $validated = $request->validate([
            'name_dv' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'description_dv' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'icon' => ['required', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $pillar->update($validated);

        return redirect()->route('admin.framework.pillars.index')->with('status', __('admin.framework.pillar_updated'));
    }
}
