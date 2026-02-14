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

        // --- Analytics: Budget Requests ---
        $budgetTotal = $requests->sum('amount');
        $budgetApproved = $requests->where('status', 'Approved')->sum('amount');
        $budgetPending = $requests->where('status', 'Pending')->sum('amount');
        $budgetRejected = $requests->where('status', 'Rejected')->sum('amount');
        $budgetByStatus = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'counts' => [
                $requests->where('status', 'Approved')->count(),
                $requests->where('status', 'Pending')->count(),
                $requests->where('status', 'Rejected')->count(),
            ],
            'amounts' => [$budgetApproved, $budgetPending, $budgetRejected],
        ];
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'label' => $date->format('M Y'),
                'amount' => \App\Models\BudgetRequest::where('employee_id', $employeeId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount'),
            ]);
        }
        $budgetByMonth = $months;

        // --- Analytics: Payment Portal (Collections) ---
        $paymentsTotal = $collections->sum('amount_paid');
        $statuses = ['Paid', 'Pending', 'Overdue'];
        $paymentsByStatus = [
            'labels' => $statuses,
            'counts' => array_map(fn ($s) => $collections->where('status', $s)->count(), $statuses),
            'amounts' => array_map(fn ($s) => $collections->where('status', $s)->sum('amount_paid'), $statuses),
        ];
        $paymentsMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $paymentsMonths->push([
                'label' => $date->format('M Y'),
                'amount' => \App\Models\Collection::where('employee_id', $employeeId)
                    ->whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount_paid'),
            ]);
        }
        $paymentsByMonth = $paymentsMonths;

        return view('employee.dashboard', compact(
            'employee', 'requests', 'collections',
            'budgetTotal', 'budgetByStatus', 'budgetByMonth',
            'paymentsTotal', 'paymentsByStatus', 'paymentsByMonth'
        ));
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

    public function paymentstore(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'customer_name' => 'required|string|max:255',
            'amount_due' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Auto-generate invoice number (INV-001, INV-002, etc.)
        $last = Collection::latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $validated['invoice_number'] = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Create the collection record
        $collection = Collection::create($validated);

        // Auto-create Journal Entry when Paid
        if ($collection->status === 'Paid') {
            $this->createJournalEntries($collection);
        }

        return redirect()->back()->with('success', 'Collection record added successfully.');
    }
}