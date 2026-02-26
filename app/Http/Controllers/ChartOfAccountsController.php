<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    // Display all accounts with computed totals
    public function index()
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        // Calculate totals based on account type so it works consistently
        $totalAssets = $accounts->where('account_type', 'Asset')->sum('balance');
        $totalLiabilities = $accounts->where('account_type', 'Liability')->sum('balance');
        $totalEquity = $accounts->where('account_type', 'Equity')->sum('balance');

        return view('finance.chart_of_accounts', compact(
            'accounts',
            'totalAssets',
            'totalLiabilities',
            'totalEquity'
        ));
    }

    // Store new account
    public function store(Request $request)
    {
        $request->validate([
            'account_code' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'balance' => 'nullable|numeric|min:0',
        ]);

        ChartOfAccount::create($request->all());

        return redirect()->back()->with('success', 'Account Added Successfully.');
    }

    /**
     * Show receipt for an account (system-generated if none exists).
     */
    public function receipt($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $receiptNumber = 'COA-' . $account->account_code . '-' . str_pad((string) $account->id, 4, '0', STR_PAD_LEFT);
        return view('finance.chart_of_account_receipt', compact('account', 'receiptNumber'));
    }

    // Fetch single account
    public function show($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        return response()->json($account);
    }

    // Update account
    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $account->update($request->all());

        return redirect()->back()->with('success', 'Account updated Successfully.');
    }

    // Delete account
    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $account->delete();

        return redirect()->back()->with('success', 'Account deleted Successfully.');
    }
}