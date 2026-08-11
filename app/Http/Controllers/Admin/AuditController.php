<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(): View
    {
        return view('admin.audit.index', [
            'activities' => Activity::with('causer', 'subject')->latest()->paginate(30),
        ]);
    }
}
