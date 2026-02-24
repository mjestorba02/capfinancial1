<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use App\Models\BudgetOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Collection;
use App\Models\Employee;
use App\Models\Payable;
use Illuminate\Support\Str;
use PDF;

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

    public function budgetRequests()
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to access budget requests.']);
        }
        $employeeId = Session::get('employee_id');
        $employee = \App\Models\Employee::find($employeeId);
        $requests = \App\Models\BudgetRequest::where('employee_id', $employeeId)->latest()->get();

        // Monthly per-employee budget limit (₱50,000)
        $monthlyLimit = 50000;
        $now = now();
        $monthlyTotal = \App\Models\BudgetRequest::where('employee_id', $employeeId)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->whereIn('status', ['Pending', 'Pending Admin', 'Approved'])
            ->sum('amount');
        $remainingBudget = max(0, $monthlyLimit - $monthlyTotal);
        $canSubmitBudgetRequest = $remainingBudget > 0;

        return view('employee.budget_requests', compact(
            'employee',
            'requests',
            'monthlyLimit',
            'monthlyTotal',
            'remainingBudget',
            'canSubmitBudgetRequest'
        ));
    }

    public function paymentPortal()
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to access the payment portal.']);
        }
        $employeeId = Session::get('employee_id');
        $employee = \App\Models\Employee::find($employeeId);
        $collections = \App\Models\Collection::where('employee_id', $employeeId)->latest()->get();
        return view('employee.payment_portal', compact('employee', 'collections'));
    }

    public function store(Request $request)
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to submit a request.']);
        }

        $request->validate([
            'purpose'   => 'required|string|max:255',
            'amount'    => 'required|numeric|min:1|max:5000000',
            'details'   => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpeg,jpg,png,gif|max:5120', // 5MB
        ]);

        $employeeId = Session::get('employee_id');
        $employee = \App\Models\Employee::find($employeeId);
        $employeeName = $employee ? $employee->name : 'Unknown';

        // Enforce per-employee monthly limit of ₱50,000 on submission
        $monthlyLimit = 50000;
        $now = now();
        $currentMonthTotal = BudgetRequest::where('employee_id', $employeeId)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->whereIn('status', ['Pending', 'Pending Admin', 'Approved'])
            ->sum('amount');

        if (($currentMonthTotal + (float) $request->amount) > $monthlyLimit) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'You have reached the monthly budget request limit of ₱50,000. You cannot submit additional requests this month.');
        }

        $last = BudgetRequest::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) preg_replace('/\D/', '', $last->request_id)) + 1 : 1;
        $request_id = 'REQ-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('budget_request_attachments', 'public');
        }

        BudgetRequest::create([
            'request_id'       => $request_id,
            'employee_id'      => $employeeId,
            'name'             => $employeeName,
            'department'       => Session::get('employee_department') ?? ($employee->department ?? 'General'),
            'purpose'          => $request->purpose,
            'amount'           => $request->amount,
            'details'          => $request->details,
            'attachment_path'  => $attachmentPath,
            'status'           => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Budget request submitted! Request ID: ' . $request_id);
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

    /**
     * Budget module: list approved budget requests; employee can order materials and create receipts.
     * Orders appear in Admin/HR Accounts Receivable - Collections as "Ordered".
     */
    public function budget()
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to access the Budget module.']);
        }
        $employeeId = Session::get('employee_id');
        $employee = Employee::find($employeeId);
        $approvedRequests = BudgetRequest::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->with('budgetOrders')
            ->latest()
            ->get();
        $orders = BudgetOrder::where('employee_id', $employeeId)->with('budgetRequest')->latest()->get();
        return view('employee.budget', compact('employee', 'approvedRequests', 'orders'));
    }

    /**
     * Store a material order from an approved budget. Creates BudgetOrder and Collection (status Ordered).
     */
    public function orderStore(Request $request)
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login')
                ->withErrors(['login' => 'Please log in to place an order.']);
        }

        $request->validate([
            'budget_request_id' => 'required|exists:budget_requests,id',
            'material_description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $employeeId = Session::get('employee_id');
        $budgetRequest = BudgetRequest::where('id', $request->budget_request_id)
            ->where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->firstOrFail();

        $amount = (float) $request->amount;
        $alreadyUsed = $budgetRequest->budgetOrders()->sum('amount');
        $remaining = $budgetRequest->amount - $alreadyUsed;
        if ($amount > $remaining) {
            return redirect()->back()->with('error', 'Amount exceeds remaining budget (₱' . number_format($remaining, 2) . ').');
        }

        $employee = Employee::find($employeeId);
        $customerName = $employee->name ?? 'Employee';

        $lastReceipt = BudgetOrder::orderByDesc('id')->first();
        $nextNum = $lastReceipt ? ((int) preg_replace('/\D/', '', $lastReceipt->receipt_number)) + 1 : 1;
        $receiptNumber = 'RCP-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $collection = Collection::create([
            'employee_id' => $employeeId,
            'budget_request_id' => $budgetRequest->id,
            'customer_name' => $customerName,
            'invoice_number' => $receiptNumber,
            'amount_due' => $amount,
            'amount_paid' => $amount,
            'status' => 'Ordered',
            'payment_date' => now(),
            'remarks' => 'Budget order: ' . $request->material_description,
        ]);

        $order = BudgetOrder::create([
            'budget_request_id' => $budgetRequest->id,
            'employee_id' => $employeeId,
            'material_description' => $request->material_description,
            'amount' => $amount,
            'receipt_number' => $receiptNumber,
            'collection_id' => $collection->id,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('employee.budget')->with('success', 'Order placed. Receipt #' . $receiptNumber . '. It will appear in Accounts Receivable - Collections as Ordered.');
    }

    /**
     * View receipt (HTML).
     */
    public function receipt($id)
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login');
        }
        $order = BudgetOrder::where('id', $id)
            ->where('employee_id', Session::get('employee_id'))
            ->with(['budgetRequest', 'employee'])
            ->firstOrFail();
        return view('employee.receipt', compact('order'));
    }

    /**
     * Download receipt as PDF.
     */
    public function receiptPdf($id)
    {
        if (!Session::has('employee_id')) {
            return redirect()->route('employee.login');
        }
        $order = BudgetOrder::where('id', $id)
            ->where('employee_id', Session::get('employee_id'))
            ->with(['budgetRequest', 'employee'])
            ->firstOrFail();

        $pdf = PDF::loadView('employee.receipt_pdf', compact('order'));
        $filename = 'receipt-' . $order->receipt_number . '.pdf';
        return $pdf->download($filename);
    }
}