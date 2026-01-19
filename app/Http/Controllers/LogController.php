<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::query()
            ->with(['user', 'store', 'actor', 'model'])
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $query->where('description', 'LIKE', "%{$request->search}%");
        }

        $logs = $query->paginate(30);

        $actions = Log::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('user.logs.index', compact('logs', 'actions'));
    }
}
