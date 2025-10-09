<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Http\Request;

class ClaimsController extends Controller
{
    public function index()
    {
        $claims = Claim::with('user')->latest()->get();
        $employees = User::all();

        return view('hr.claims', compact('claims', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'              => 'required|exists:users,id',
            'type_of_claim'        => 'required|string|max:255',
            'attached_document'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'claim_date'           => 'required|date',
            'claim_amount'         => 'required|numeric|min:0',
            'reimbursement_amount' => 'nullable|numeric|min:0',
            'reimbursement_date'   => 'nullable|date',
            'status'               => 'required|in:Paid,Pending,Denied',
        ]);

        $filePath = null;
        if ($request->hasFile('attached_document')) {
            $filePath = $request->file('attached_document')->store('claims_documents', 'public');
        }

        Claim::create([
            'claim_id'             => 'CLM' . str_pad(Claim::count() + 1, 3, '0', STR_PAD_LEFT),
            'user_id'              => $request->user_id,
            'type_of_claim'        => $request->type_of_claim,
            'attached_document'    => $filePath,
            'claim_date'           => $request->claim_date,
            'claim_amount'         => $request->claim_amount,
            'reimbursement_amount' => $request->reimbursement_amount ?? 0,
            'reimbursement_date'   => $request->reimbursement_date,
            'status'               => $request->status,
        ]);

        return redirect()->route('claims.index')->with('success', 'Claim added successfully!');
    }
}