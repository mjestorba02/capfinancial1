<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        // ========== COLLECTIONS (ACCOUNTS RECEIVABLE: Paid Only) ==========
        $collectionQuery = Collection::query()->where('status', 'Paid');

        // Optional filters
        if ($request->filled('r_search')) {
            $s = $request->r_search;
            $collectionQuery->where(fn($q) =>
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('invoice_number', 'like', "%{$s}%")
            );
        }

        if ($request->filled('r_from')) {
            $collectionQuery->whereDate('payment_date', '>=', $request->r_from);
        }

        if ($request->filled('r_to')) {
            $collectionQuery->whereDate('payment_date', '<=', $request->r_to);
        }

        // ========== PAYABLES ==========
        $payableQuery = Payable::query();

        if ($request->filled('p_search')) {
            $s = $request->p_search;
            $payableQuery->where(fn($q) =>
                $q->where('vendor', 'like', "%{$s}%")
                  ->orWhere('invoice_number', 'like', "%{$s}%")
            );
        }

        if ($request->filled('p_status')) {
            $payableQuery->where('status', $request->p_status);
        }

        if ($request->filled('p_from')) {
            $payableQuery->whereDate('due_date', '>=', $request->p_from);
        }

        if ($request->filled('p_to')) {
            $payableQuery->whereDate('due_date', '<=', $request->p_to);
        }

        // Pagination
        $collections = $collectionQuery->orderByDesc('payment_date')->paginate(20, ['*'], 'collections_page');
        $payables = $payableQuery->orderByDesc('due_date')->paginate(20, ['*'], 'payables_page');

        // Totals (summary)
        $totalReceivables = Collection::sum('amount_due');
        $totalCollected = Collection::where('status', 'Paid')->sum('amount_paid');
        $totalUnpaidReceivables = Collection::where('status', 'Pending')->sum('amount_due');

        $totalPayables = Payable::sum('amount');
        $totalUnpaidPayables = Payable::where('status', 'Unpaid')->sum('amount');

        return view('finance.accounts', compact(
            'collections',
            'payables',
            'totalReceivables',
            'totalCollected',
            'totalUnpaidReceivables',
            'totalPayables',
            'totalUnpaidPayables'
        ));
    }

    // -----------------------
    // PAYABLE CRUD FUNCTIONS
    // -----------------------
    public function storePayable(Request $request)
    {
        $data = $request->validate([
            'vendor' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'mode_of_payment' => 'nullable|string|max:100', // ✅ added
            'due_date' => 'nullable|date',
            'payment_date' => 'nullable|date',              // ✅ added
            'status' => 'required|in:Unpaid,Paid,Overdue',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $last = Payable::orderByDesc('id')->first();
        $next = $last ? (int) Str::after($last->payment_id ?? 'PAY-000', 'PAY-') + 1 : 1;
        $data['payment_id'] = 'PAY-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        if (empty($data['invoice_number'])) {
            $lastInvoice = Payable::orderByDesc('id')->first();
            $nextInvoiceNum = $lastInvoice ? ((int) Str::after($lastInvoice->invoice_number ?? 'INV-000', 'INV-') + 1) : 1;
            $data['invoice_number'] = 'INV-' . str_pad($nextInvoiceNum, 3, '0', STR_PAD_LEFT);
        }

        Payable::create($data);

        return redirect()->route('accounts.index')->with('success', 'Payable added successfully.');
    }

    public function updatePayable(Request $request, Payable $payable)
    {
        $data = $request->validate([
            'vendor' => 'required|string|max:255',
            'invoice_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'mode_of_payment' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:Unpaid,Paid,Overdue',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // ✅ Preserve original payment_date if left blank
        if (empty($data['payment_date'])) {
            unset($data['payment_date']);
        }

        // ✅ Preserve original due_date if left blank
        if (empty($data['due_date'])) {
            unset($data['due_date']);
        }

        $payable->update($data);

        return redirect()->route('accounts.index')->with('success', 'Payable updated successfully.');
    }

    public function destroyPayable(Payable $payable)
    {
        $payable->delete();
        return redirect()->route('accounts.index')->with('success', 'Payable deleted successfully.');
    }
}