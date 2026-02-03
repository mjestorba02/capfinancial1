<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BudgetRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = BudgetRequest::orderBy('created_at', 'desc');

        // Filter by date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [
                $request->input('from') . ' 00:00:00',
                $request->input('to') . ' 23:59:59'
            ]);
        } elseif ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        } elseif ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        // Optional: filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->get();

        return view('finance.budget_requests', compact('requests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => 'required|string|max:255',
            'purpose' => 'required|string',
            'amount' => 'required|numeric|min:1|max:50000',
        ]);

        // Generate Request ID
        $last = BudgetRequest::orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->request_id, 4)) + 1 : 1;
        $request_id = 'REQ-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Assume authenticated user has an employee relation
        $employee_id = Auth::user()->employee->id ?? null; // Adjust as needed

        BudgetRequest::create([
            'request_id' => $request_id,
            'employee_id' => $employee_id,
            'department' => $request->department,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'status' => 'Pending', // default status
        ]);

        return redirect()->back()->with('success', 'Budget Request Added Successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department' => 'required|string|max:255',
            'purpose' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $budget = BudgetRequest::findOrFail($id);
        $budget->update([
            'department' => $request->department,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        // If Approved → push to planning table
        if ($request->status === 'Approved') {
            DB::table('planning')->insert([
                'request_id' => $budget->request_id,
                'department' => $budget->department,
                'purpose' => $budget->purpose,
                'amount' => $budget->amount,
                'approved_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Budget Request Updated Successfully.');
    }

    public function destroy($id)
    {
        BudgetRequest::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Budget Request Deleted Successfully.');
    }

    public function approve($id)
    {
        $budget = BudgetRequest::findOrFail($id);

        // Update the status
        $budget->update([
            'status' => 'Approved',
            'remarks' => 'Approved automatically by finance officer',
        ]);

        // Also push to planning table
        \DB::table('planning')->insert([
            'request_id' => $budget->request_id,
            'department' => $budget->department,
            'purpose' => $budget->purpose,
            'amount' => $budget->amount,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Budget Request Approved Successfully.');
    }

    // 🔹 Upload Image
    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $budget = BudgetRequest::findOrFail($id);

        // Delete old image if exists
        if ($budget->image_path && \Storage::exists($budget->image_path)) {
            \Storage::delete($budget->image_path);
        }

        // Store new image
        $path = $request->file('image')->store('budget_request_images', 'public');
        $budget->update(['image_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image_url' => asset('storage/' . $path),
        ]);
    }

    // 🔹 Delete Image
    public function deleteImage($id)
    {
        $budget = BudgetRequest::findOrFail($id);

        if ($budget->image_path && \Storage::exists($budget->image_path)) {
            \Storage::delete($budget->image_path);
            $budget->update(['image_path' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}