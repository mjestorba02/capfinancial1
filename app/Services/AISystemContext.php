<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\BudgetRequest;
use App\Models\Payable;
use App\Models\Disbursement;
use App\Models\Allocation;
use App\Models\User;

/**
 * AI System Context Provider
 * Extracts current financial system data to provide context to AI responses
 */
class AISystemContext
{
    /**
     * Get comprehensive system context for AI processing
     */
    public static function getContext()
    {
        $context = "### FINANCIAL SYSTEM CONTEXT\n\n";
        $context .= "## Current System Status\n";
        $context .= self::getFinancialSummary();
        $context .= "\n" . self::getRecentTransactions();
        $context .= "\n" . self::getBudgetStatus();
        $context .= "\n" . self::getPayableStatus();
        $context .= "\n";

        return $context;
    }

    /**
     * Get financial summary statistics
     */
    private static function getFinancialSummary()
    {
        $totalCollections = Collection::sum('amount_paid') ?: 0;
        $totalPayables = Payable::where('status', 'Unpaid')->sum('amount') ?: 0;
        $totalDisbursements = Disbursement::sum('amount') ?: 0;
        $totalBudgetRequests = BudgetRequest::count();
        $pendingBudgets = BudgetRequest::where('status', 'Pending')->count();
        $approvedBudgets = BudgetRequest::where('status', 'Approved')->count();
        $userCount = User::count();

        return "- **Total Collections (Paid):** ₱" . number_format($totalCollections, 2) . "\n" .
               "- **Total Payables (Unpaid):** ₱" . number_format($totalPayables, 2) . "\n" .
               "- **Total Disbursements:** ₱" . number_format($totalDisbursements, 2) . "\n" .
               "- **Budget Requests:** {$totalBudgetRequests} total ({$pendingBudgets} pending, {$approvedBudgets} approved)\n" .
               "- **Active Users:** {$userCount}\n";
    }

    /**
     * Get recent transactions summary
     */
    private static function getRecentTransactions()
    {
        $context = "## Recent Activity (Last 10 Days)\n";

        $recentCollections = Collection::whereDate('created_at', '>=', now()->subDays(10))->count();
        $recentDisbursements = Disbursement::whereDate('created_at', '>=', now()->subDays(10))->count();
        $recentPayables = Payable::whereDate('created_at', '>=', now()->subDays(10))->count();

        $context .= "- **New Collections:** {$recentCollections}\n" .
                   "- **New Disbursements:** {$recentDisbursements}\n" .
                   "- **New Payables:** {$recentPayables}\n";

        return $context;
    }

    /**
     * Get budget allocation status
     */
    private static function getBudgetStatus()
    {
        $context = "## Budget Allocations by Department\n";

        $allocations = Allocation::groupBy('department')
            ->selectRaw('department, SUM(allocated) as total_allocated, SUM(used) as total_used')
            ->get();

        if ($allocations->isEmpty()) {
            return $context . "- No budget allocations yet\n";
        }

        foreach ($allocations as $alloc) {
            $remaining = $alloc->total_allocated - $alloc->total_used;
            $utilization = $alloc->total_allocated > 0 ? round(($alloc->total_used / $alloc->total_allocated) * 100, 2) : 0;
            $context .= "- **{$alloc->department}:** ₱" . number_format($alloc->total_allocated, 2) . 
                       " allocated, ₱" . number_format($alloc->total_used, 2) . 
                       " used ({$utilization}%), ₱" . number_format($remaining, 2) . " remaining\n";
        }

        return $context;
    }

    /**
     * Get payable status
     */
    private static function getPayableStatus()
    {
        $context = "## Payables Status\n";

        $totalPayables = Payable::where('status', 'Unpaid')->sum('amount') ?: 0;
        $payableCount = Payable::where('status', 'Unpaid')->count();
        $overduePayables = Payable::where('status', 'Unpaid')
            ->whereDate('due_date', '<', now())
            ->sum('amount') ?: 0;

        $context .= "- **Total Unpaid:** ₱" . number_format($totalPayables, 2) . " ({$payableCount} items)\n" .
                   "- **Overdue Amount:** ₱" . number_format($overduePayables, 2) . "\n";

        return $context;
    }
}
