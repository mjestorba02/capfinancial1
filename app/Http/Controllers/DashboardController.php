<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Claim;
use App\Models\LeaveType;
use App\Models\Timesheet;
use App\Models\TimeTracking;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    public function search(\Illuminate\Http\Request $request)
    {
        $query = $request->input('q');
        $users = User::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('department', 'like', "%$query%")
            ->orWhere('position', 'like', "%$query%")
            ->get();
        return view('search-results', compact('query', 'users'));
    }
}