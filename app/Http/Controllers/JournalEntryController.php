<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of journal entries with optional filters.
     */
    public function index(Request $request)
    {
        $query = JournalEntry::query();

        // ✅ Filter by account
        if ($request->filled('account')) {
            $query->where('account', 'like', '%' . $request->account . '%');
        }

        // ✅ Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->to);
        }

        // ✅ Sort newest first
        $journals = $query->orderBy('entry_date', 'desc')->get();

        return view('finance.journal_entries', compact('journals'));
    }

    /**
     * Store a newly created journal entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account' => 'required|string|max:255',
            'type' => 'required|in:Debit,Credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'entry_date' => 'required|date',
        ]);

        // ✅ Auto-assign debit/credit based on type
        $credit = $request->type === 'Credit' ? $request->amount : 0;
        $debit = $request->type === 'Debit' ? $request->amount : 0;

        JournalEntry::create([
            'account' => $request->account,
            'type' => $request->type,
            'credit' => $credit,
            'debit' => $debit,
            'description' => $request->description,
            'entry_date' => $request->entry_date,
            'source_module' => 'Manual Entry',
            'reference_id' => null,
        ]);

        return redirect()->route('journal_entries.index')
                         ->with('success', 'Journal entry added successfully.');
    }

    /**
     * Update an existing journal entry.
     */
    public function update(Request $request, $id)
    {
        $journal = JournalEntry::findOrFail($id);

        $request->validate([
            'account' => 'required|string|max:255',
            'credit' => 'nullable|numeric|min:0',
            'debit' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'entry_date' => 'required|date',
        ]);

        $journal->update([
            'account' => $request->account,
            'credit' => $request->credit ?? 0,
            'debit' => $request->debit ?? 0,
            'description' => $request->description,
            'entry_date' => $request->entry_date,
        ]);

        return redirect()->route('journal_entries.index')
                         ->with('success', 'Journal entry updated successfully.');
    }

    /**
     * Remove a journal entry.
     */
    public function destroy($id)
    {
        $journal = JournalEntry::findOrFail($id);
        $journal->delete();

        return redirect()->route('journal_entries.index')
                         ->with('success', 'Journal entry deleted successfully.');
    }
}