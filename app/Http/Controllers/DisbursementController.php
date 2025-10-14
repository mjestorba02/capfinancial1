<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use App\Models\JournalEntry;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisbursementController extends Controller
{
    public function index()
    {
        $disbursements = Disbursement::orderByDesc('id')->get();
        return view('finance.disbursement', compact('disbursements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|max:50',
            'disbursement_date' => 'required|date',
        ]);

        // 🔹 Auto-generate next voucher number
        $last = Disbursement::latest('id')->first();
        $next = $last ? (int) substr($last->voucher_no, 4) + 1 : 1;
        $voucher_no = 'VCH-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        // 🔹 Create Disbursement record
        $disbursement = Disbursement::create([
            'voucher_no' => $voucher_no,
            'vendor' => $request->vendor,
            'category' => $request->category,
            'amount' => $request->amount,
            'status' => $request->status,
            'disbursement_date' => $request->disbursement_date,
        ]);

        // =====================================================
        // 🔸 CREATE RELATED ACCOUNT PAYABLE ENTRY
        // =====================================================

        // Auto-generate Payment ID
        $lastPayable = Payable::orderByDesc('id')->first();
        $nextPayable = $lastPayable ? (int) Str::after($lastPayable->payment_id ?? 'PAY-000', 'PAY-') + 1 : 1;
        $payment_id = 'PAY-' . str_pad($nextPayable, 3, '0', STR_PAD_LEFT);

        $payable = Payable::create([
            'payment_id' => $payment_id,
            'vendor' => $request->vendor,
            'invoice_number' => 'INV-' . str_pad($nextPayable, 3, '0', STR_PAD_LEFT),
            'amount' => $request->amount,
            'mode_of_payment' => 'Bank Transfer', // default, can be updated later
            'due_date' => $request->disbursement_date,
            'payment_date' => null,
            'status' => 'Unpaid',
            'remarks' => 'Generated automatically from Disbursement #' . $voucher_no,
        ]);

        // =====================================================
        // 🔸 CREATE RELATED JOURNAL ENTRY
        // =====================================================

        JournalEntry::create([
            'account' => $request->category,
            'type' => 'Disbursement',
            'credit' => $request->amount,
            'debit' => 0,
            'description' => 'Disbursement to ' . $request->vendor,
            'entry_date' => $request->disbursement_date,
            'source_module' => 'Disbursement',
            'reference_id' => $disbursement->id,
        ]);

        // You can also log a debit entry if needed:
        JournalEntry::create([
            'account' => 'Accounts Payable',
            'type' => 'Disbursement',
            'credit' => 0,
            'debit' => $request->amount,
            'description' => 'Payable created for ' . $request->vendor,
            'entry_date' => $request->disbursement_date,
            'source_module' => 'Disbursement',
            'reference_id' => $disbursement->id,
        ]);

        // =====================================================
        // 🔸 Redirect back
        // =====================================================
        return redirect()->back()->with('success', 'Disbursement added successfully. Related Journal Entry and Payable created.');
    }

    public function show($id)
    {
        $disbursement = Disbursement::findOrFail($id);
        return view('finance.disbursement-show', compact('disbursement'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendor' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|max:50',
            'disbursement_date' => 'required|date',
        ]);

        $disb = Disbursement::findOrFail($id);
        $disb->update($request->only(['vendor', 'category', 'amount', 'status', 'disbursement_date']));

        return redirect()->back()->with('success', 'Disbursement updated successfully.');
    }

    public function destroy($id)
    {
        $disb = Disbursement::findOrFail($id);
        $disb->delete();

        return redirect()->back()->with('success', 'Disbursement deleted successfully.');
    }
}