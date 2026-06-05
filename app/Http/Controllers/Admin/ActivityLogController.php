<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs  = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();

        $stats = [
            'today'    => ActivityLog::whereDate('created_at', today())->count(),
            'logins'   => ActivityLog::where('action', 'login')->whereDate('created_at', today())->count(),
            'active'   => ActivityLog::whereDate('created_at', today())->distinct('user_id')->count('user_id'),
            'total'    => ActivityLog::count(),
        ];

        return view('activity-logs.index', compact('logs', 'users', 'stats'));
    }
}
