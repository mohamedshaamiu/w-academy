<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrameworkStrikeLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrikeLevelController extends Controller
{
    public function index(): View
    {
        return view('admin.framework.strike-levels', ['levels' => FrameworkStrikeLevel::orderBy('sort_order')->get()]);
    }

    public function edit(FrameworkStrikeLevel $strikeLevel): View
    {
        return view('admin.framework.strike-level-edit', ['level' => $strikeLevel]);
    }

    public function update(Request $request, FrameworkStrikeLevel $strikeLevel): RedirectResponse
    {
        $validated = $request->validate([
            'label_dv' => ['required', 'string', 'max:100'],
            'label_en' => ['required', 'string', 'max:100'],
            'type_dv' => ['required', 'string', 'max:100'],
            'type_en' => ['required', 'string', 'max:100'],
            'action_dv' => ['required', 'string'],
            'action_en' => ['required', 'string'],
            'parent_role_dv' => ['required', 'string'],
            'parent_role_en' => ['required', 'string'],
            'triggers_timeout' => ['boolean'],
            'timeout_minutes_min' => ['nullable', 'integer', 'min:1'],
            'timeout_minutes_max' => ['nullable', 'integer', 'gte:timeout_minutes_min'],
            'triggers_parent_alert' => ['boolean'],
            'triggers_meeting' => ['boolean'],
            'triggers_suspension' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $strikeLevel->update($validated);

        return redirect()->route('admin.framework.strike-levels.index')->with('status', __('admin.framework.strike_level_updated'));
    }
}
