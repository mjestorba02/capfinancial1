<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Allocation;
use App\Models\BudgetRequest;
use App\Models\Planning;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function index()
    {
        // Load budget requests and allocations for the page
        $requests = BudgetRequest::where('status', 'Approved')
            ->orderByDesc('created_at')
            ->get();

        $allocations = Allocation::orderByDesc('created_at')->get();

        // Monthly department usage (current month) against the ₱50,000 cap
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $departmentMonthlyUsage = Allocation::select('department', DB::raw('SUM(allocated) as total_allocated'))
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->groupBy('department')
            ->pluck('total_allocated', 'department');

        $monthlyLimit = 50000;

        return view('finance.allocations', compact(
            'requests',
            'allocations',
            'departmentMonthlyUsage',
            'monthlyLimit'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_request_id' => 'nullable|exists:budget_requests,id',
            'department' => 'required|string|max:255',
            'project' => 'required|string|max:255',
            'allocated' => 'required|numeric|min:0',
        ]);

        $monthlyLimit = 50000;
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $currentTotalForDepartment = Allocation::where('department', $validated['department'])
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('allocated');

        if (($currentTotalForDepartment + $validated['allocated']) > $monthlyLimit) {
            $remaining = $monthlyLimit - $currentTotalForDepartment;
            return back()
                ->withInput()
                ->with('error', 'This allocation would exceed the monthly department limit of ₱50,000. Remaining limit for this department this month is ₱' . number_format(max(0, $remaining), 2) . '.');
        }

        DB::transaction(function() use ($validated) {
            $allocation = Allocation::create([
                'budget_request_id' => $validated['budget_request_id'] ?? null,
                'department' => $validated['department'],
                'project' => $validated['project'],
                'allocated' => $validated['allocated'],
                'used' => 0,
            ]);

            // If this was created from an existing budget request, mark request Approved and add to planning
            if (!empty($validated['budget_request_id'])) {
                $br = BudgetRequest::find($validated['budget_request_id']);
                if ($br) {
                    $br->update(['status' => 'Approved']);
                    Planning::create([
                        'request_id' => $br->request_id,
                        'department' => $br->department,
                        'purpose' => $br->purpose,
                        'amount' => $br->amount,
                        'approved_at' => now(),
                    ]);
                }
            }

            // Create journal entries
            $this->createJournalOnAllocation($allocation);
        });

        return redirect()->route('finance.allocations.index')->with('success', 'Allocation created successfully.');
    }

    public function update(Request $request, Allocation $allocation)
    {
        $validated = $request->validate([
            'department' => 'required|string|max:255',
            'project' => 'required|string|max:255',
            'allocated' => 'required|numeric|min:0',
            'used' => 'nullable|numeric|min:0',
        ]);

        $monthlyLimit = 50000;
        $monthStart = Carbon::parse($allocation->created_at)->startOfMonth();
        $monthEnd = Carbon::parse($allocation->created_at)->endOfMonth();

        $currentTotalForDepartment = Allocation::where('department', $validated['department'])
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('id', '!=', $allocation->id)
            ->sum('allocated');

        if (($currentTotalForDepartment + $validated['allocated']) > $monthlyLimit) {
            $remaining = $monthlyLimit - $currentTotalForDepartment;
            return back()
                ->withInput()
                ->with('error', 'Updating this allocation would exceed the monthly department limit of ₱50,000. Remaining limit for this department for that month is ₱' . number_format(max(0, $remaining), 2) . '.');
        }

        DB::transaction(function() use ($allocation, $validated) {
            $allocation->update([
                'department' => $validated['department'],
                'project' => $validated['project'],
                'allocated' => $validated['allocated'],
                'used' => $validated['used'] ?? $allocation->used,
            ]);

            // Re-create journal entries (simple approach: create another set to reflect changed allocation)
            $this->createJournalOnAllocation($allocation);
        });

        return redirect()->route('finance.allocations.index')->with('success', 'Allocation updated successfully.');
    }

    public function destroy(Allocation $allocation)
    {
        $allocation->delete();
        return redirect()->route('finance.allocations.index')->with('success', 'Allocation deleted.');
    }

    // separate endpoint for updating 'used' amount (if you want a dedicated route)
    public function updateUsed(Request $request, Allocation $allocation)
    {
        $validated = $request->validate([
            'used' => 'required|numeric|min:0',
        ]);

        $allocation->update(['used' => $validated['used']]);

        return redirect()->route('finance.allocations.index')->with('success', 'Used value updated.');
    }

    /**
     * Create journal entries for allocation.
     * Debit: "Budget Expense" — Debit = allocated
     * Credit: "Budget Reserve" — Credit = allocated
     *
     * You can change accounts as needed.
     */
    private function createJournalOnAllocation(Allocation $allocation)
    {
        $amount = $allocation->allocated;
        $today = Carbon::now()->toDateString();

        JournalEntry::create([
            'account' => 'Budget Expense',
            'type' => 'Debit',
            'debit' => $amount,
            'credit' => 0,
            'description' => "Allocation for {$allocation->department} / {$allocation->project}",
            'entry_date' => $today,
            'source_module' => 'Allocation',
            'reference_id' => $allocation->id,
        ]);

        JournalEntry::create([
            'account' => 'Budget Reserve',
            'type' => 'Credit',
            'debit' => 0,
            'credit' => $amount,
            'description' => "Allocation reserved for {$allocation->department} / {$allocation->project}",
            'entry_date' => $today,
            'source_module' => 'Allocation',
            'reference_id' => $allocation->id,
        ]);
    }
}