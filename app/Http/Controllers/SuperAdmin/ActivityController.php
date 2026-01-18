<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivityLogsExport;

class ActivityController extends Controller
{
    /**
     * Display activity logs.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with(['user', 'restaurant']);

        // Filters
        if ($request->filled('restaurant')) {
            $query->where('restaurant_id', $request->restaurant);
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(50)->withQueryString();

        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        // Get unique actions for filter
        $actions = ActivityLog::distinct()->pluck('action');

        return view('pages.super-admin.activity', compact('activities', 'restaurants', 'actions'));
    }

    /**
     * Display a single activity log.
     */
    public function show(ActivityLog $log): View
    {
        $log->load(['user', 'restaurant']);
        
        return view('pages.super-admin.activity-show', compact('log'));
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with(['user', 'restaurant']);

        // Apply same filters as index
        if ($request->filled('restaurant')) {
            $query->where('restaurant_id', $request->restaurant);
        }

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->get();

        return Excel::download(
            new ActivityLogsExport($activities),
            "logs_activite_" . now()->format('Ymd_His') . ".xlsx"
        );
    }
}

