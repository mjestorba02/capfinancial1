<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountsController extends Controller
{
    // Display all accounts
    public function index()
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        // Calculate totals
        $totalAssets = $accounts->where('category', 'like', '%Asset%')->sum('balance');
        $totalLiabilities = $accounts->where('category', 'like', '%Liabil%')->sum('balance');
        $totalEquity = $accounts->where('category', 'like', '%Equity%')->sum('balance');

        return view('finance.chart_of_accounts', compact('accounts', 'totalAssets', 'totalLiabilities', 'totalEquity'));
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