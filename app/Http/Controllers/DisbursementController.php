<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use Illuminate\Http\Request;

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

        // Auto-generate next voucher number
        $last = Disbursement::latest('id')->first();
        $next = $last ? (int) substr($last->voucher_no, 4) + 1 : 1;
        $voucher_no = 'VCH-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        Disbursement::create([
            'voucher_no' => $voucher_no,
            'vendor' => $request->vendor,
            'category' => $request->category,
            'amount' => $request->amount,
            'status' => $request->status,
            'disbursement_date' => $request->disbursement_date,
        ]);

        return redirect()->back()->with('success', 'Disbursement added successfully.');
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