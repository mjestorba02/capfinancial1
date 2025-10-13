<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Collection;
use App\Models\Payable;
use Illuminate\Support\Str;

class EmployeeBudgetController extends Controller
{
    public function index()
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to access your dashboard.']);
        }

        $employeeId = Session::get('employee_id');

        // Fetch employee details
        $employee = \App\Models\Employee::find($employeeId);

        // Fetch related requests
        $requests = \App\Models\BudgetRequest::where('employee_id', $employeeId)
            ->latest()
            ->get();

        // Fetch related collections
        $collections = \App\Models\Collection::where('employee_id', $employeeId)
            ->latest()
            ->get();

        return view('employee.dashboard', compact('employee', 'requests', 'collections'));
    }

    public function store(Request $request)
    {
        // ensure employee is logged in
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to submit a request.']);
        }

        $request->validate([
            'purpose' => 'required|string|max:255',
            'amount'  => 'required|numeric|min:1|max:5000000',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // 🔹 Generate unique request_id (like REQ-001)
        $last = BudgetRequest::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->request_id, 4)) + 1 : 1;
        $request_id = 'REQ-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        BudgetRequest::create([
            'request_id'  => $request_id,
            'employee_id' => Session::get('employee_id'),
            'department'  => Session::get('employee_department') ?? 'Finance',
            'purpose'     => $request->purpose,
            'amount'      => $request->amount,
            'remarks'     => $request->remarks,
            'status'      => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Budget request submitted!');
    }
}