<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\JournalEntry;
use Carbon\Carbon;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::latest()->get();
        return view('finance.collections', compact('collections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'customer_name' => 'required|string|max:255',
            'amount_due' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Auto-generate invoice number (INV-001, INV-002, etc.)
        $last = Collection::latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;
        $validated['invoice_number'] = 'INV-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Create the collection record
        $collection = Collection::create($validated);

        // Auto-create Journal Entry when Paid
        if ($collection->status === 'Paid') {
            $this->createJournalEntries($collection);
        }

        return redirect()->back()->with('success', 'Collection record added successfully.');
    }

    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'amount_due' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $collection->update($validated);

        // Auto-create Journal Entry if marked Paid
        if ($collection->status === 'Paid') {
            $this->createJournalEntries($collection);
        }

        return redirect()->back()->with('success', 'Collection record updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        return redirect()->back()->with('success', 'Collection record deleted successfully.');
    }

    // Journal Entry Automation
    private function createJournalEntries($collection)
    {
        $amount = $collection->amount_paid ?: $collection->amount_due;
        $today = Carbon::now()->toDateString();

        // Debit: Cash / Bank
        JournalEntry::create([
            'account' => 'Cash',
            'type' => 'Debit',
            'debit' => $amount,
            'credit' => 0,
            'description' => 'Collection received from ' . $collection->customer_name,
            'entry_date' => $today,
            'source_module' => 'Collections',
            'reference_id' => $collection->id,
        ]);

        // Credit: Accounts Receivable
        JournalEntry::create([
            'account' => 'Accounts Receivable',
            'type' => 'Credit',
            'debit' => 0,
            'credit' => $amount,
            'description' => 'Collection payment recorded for ' . $collection->customer_name,
            'entry_date' => $today,
            'source_module' => 'Collections',
            'reference_id' => $collection->id,
        ]);
    }

    public function approve(Collection $collection)
    {
        if ($collection->status === 'Paid') {
            return redirect()->back()->with('success', 'This collection is already marked as Paid.');
        }

        // Update status
        $collection->update([
            'status' => 'Paid',
            'amount_paid' => $collection->amount_due,
            'payment_date' => now(),
        ]);

        // Create journal entries
        $this->createJournalEntries($collection);

        return redirect()->back()->with('success', 'Collection approved and journal entry recorded.');
    }

}