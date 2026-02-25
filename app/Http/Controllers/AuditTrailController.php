<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $query = AuditTrail::query()->latest();

        // Admin can see HR + employee audit trails
        if ($user->isAdmin()) {
            $query->whereIn('actor_type', ['hr', 'employee']);
        }
        // HR can see only employee audit trails
        elseif ($user->isHr()) {
            $query->where('actor_type', 'employee');
        } else {
            abort(403, 'Only Admin and HR can access the audit trail.');
        }

        // Optional filters (date range)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50);

        return view('audit_trails.index', compact('logs', 'user'));
    }
}

