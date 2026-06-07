<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->requireRole('admin');

        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(30);

        return view('activity_logs.index', compact('activities'));
    }
}
