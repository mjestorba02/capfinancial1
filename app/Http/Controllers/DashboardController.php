<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Collection;
use App\Models\BudgetRequest;
use App\Models\Payable;
use App\Models\Disbursement;
use App\Models\Allocation;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Financial summary ---
        $totalCollections = Collection::sum('amount_paid') ?: 0;
        $budgetRequests = BudgetRequest::count();
        $totalPayables = Payable::where('status', 'Unpaid')->sum('amount') ?: 0;
        $totalDisbursements = Disbursement::sum('amount') ?: 0;

        // --- Progress calculations (rounded, safe) ---
        $totalFlow = $totalCollections + $totalPayables + $totalDisbursements;

        $collectionsProgress = $totalFlow > 0 ? round(($totalCollections / $totalFlow) * 100, 2) : 0;
        $payablesProgress = $totalFlow > 0 ? round(($totalPayables / $totalFlow) * 100, 2) : 0;
        // <-- singular name to match Blade: $disbursementProgress
        $disbursementProgress = $totalFlow > 0 ? round(($totalDisbursements / $totalFlow) * 100, 2) : 0;

        // --- Recent Budget Requests (limit 5) ---
        $recentBudgetRequests = BudgetRequest::with('employee')
            ->latest()
            ->take(5)
            ->get();

        // --- Recent Payables (limit 5) ---
        $recentPayables = Payable::latest()
            ->take(5)
            ->get();

        // --- Budget Allocation by Department (uses 'used' column per your model) ---
        $budgetAllocations = Allocation::select('id','department','allocated','used','project','budget_request_id')
            ->get();

        // --- Analytics: Cash flow breakdown (for pie chart) ---
        $flowBreakdown = [
            'labels' => ['Collections', 'Payables (Unpaid)', 'Disbursements'],
            'amounts' => [$totalCollections, $totalPayables, $totalDisbursements],
        ];

        // --- Analytics: Budget requests by status ---
        $budgetByStatus = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'counts' => [
                BudgetRequest::where('status', 'Approved')->count(),
                BudgetRequest::where('status', 'Pending')->count(),
                BudgetRequest::where('status', 'Rejected')->count(),
            ],
            'amounts' => [
                BudgetRequest::where('status', 'Approved')->sum('amount'),
                BudgetRequest::where('status', 'Pending')->sum('amount'),
                BudgetRequest::where('status', 'Rejected')->sum('amount'),
            ],
        ];

        // --- Analytics: Last 6 months (collections, disbursements, budget requests) ---
        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyData->push([
                'label' => $date->format('M Y'),
                'collections' => Collection::whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount_paid'),
                'disbursements' => Disbursement::whereYear('disbursement_date', $date->year)
                    ->whereMonth('disbursement_date', $date->month)
                    ->sum('amount'),
                'budget_amount' => BudgetRequest::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount'),
                'budget_count' => BudgetRequest::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        // --- Analytics: Allocation by department (allocated vs used) ---
        $allocationChart = $budgetAllocations->map(fn ($a) => [
            'department' => $a->department,
            'allocated' => (float) $a->allocated,
            'used' => (float) $a->used,
        ])->values();

        return view('dashboard', compact(
            'totalCollections',
            'budgetRequests',
            'totalPayables',
            'totalDisbursements',
            'collectionsProgress',
            'payablesProgress',
            'disbursementProgress',
            'recentBudgetRequests',
            'recentPayables',
            'budgetAllocations',
            'flowBreakdown',
            'budgetByStatus',
            'monthlyData',
            'allocationChart'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('department', 'like', "%{$query}%")
            ->orWhere('position', 'like', "%{$query}%")
            ->get();

        return view('search-results', compact('query', 'users'));
    }
}