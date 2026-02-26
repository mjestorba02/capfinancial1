<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PDF;

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

    public function exportCsv(Request $request): StreamedResponse
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        $filename = 'chart_of_accounts_' . Carbon::now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($accounts) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Account Code',
                'Account Name',
                'Account Type',
                'Category',
                'Description',
                'Balance',
            ]);

            foreach ($accounts as $account) {
                fputcsv($out, [
                    $account->account_code,
                    $account->account_name,
                    $account->account_type,
                    $account->category,
                    $account->description,
                    $account->balance,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        $totalAssets = $accounts->where('account_type', 'Asset')->sum('balance');
        $totalLiabilities = $accounts->where('account_type', 'Liability')->sum('balance');
        $totalEquity = $accounts->where('account_type', 'Equity')->sum('balance');

        $pdf = PDF::loadView('finance.exports.chart_of_accounts_pdf', compact(
            'accounts',
            'totalAssets',
            'totalLiabilities',
            'totalEquity'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('chart_of_accounts.pdf');
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