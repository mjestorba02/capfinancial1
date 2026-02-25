<?php

namespace App\Http\Controllers;

use App\Models\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditTrailService;

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

        $requests = $query->with('employee')->get();
        $user = Auth::user();

        return view('finance.budget_requests', compact('requests', 'user'));
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

        // If status is being changed to Approved, enforce per-employee monthly approval limit (₱50,000)
        if ($request->status === 'Approved' && $budget->status !== 'Approved') {
            if (! $this->canApproveForMonth($budget)) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot approve this request: the employee has reached the monthly approved budget limit of ₱50,000.');
            }
        }

        $budget->update([
            'department' => $request->department,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        if ($request->status === 'Approved' && \Schema::hasTable('planning')) {
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

        // Enforce per-employee monthly approval limit (₱50,000)
        if (! $this->canApproveForMonth($budget)) {
            return redirect()
                ->back()
                ->with('error', 'Cannot approve this request: the employee has reached the monthly approved budget limit of ₱50,000.');
        }

        $budget->update([
            'status' => 'Approved',
            'remarks' => 'Approved automatically by finance officer',
            'admin_approved_at' => now(),
        ]);
        if (\Schema::hasTable('planning')) {
            \DB::table('planning')->insert([
                'request_id' => $budget->request_id,
                'department' => $budget->department,
                'purpose' => $budget->purpose,
                'amount' => $budget->amount,
                'approved_at' => now(),
            ]);
        }
        AuditTrailService::logUser(
            Auth::user(),
            'budget_request_approved_auto',
            'Budget request ' . $budget->request_id . ' approved automatically by finance officer.',
            'budget_request',
            $budget->id
        );

        return redirect()->back()->with('success', 'Budget Request Approved Successfully.');
    }

    /** HR: Send request to Admin (status = Pending Admin) */
    public function hrApprove($id)
    {
        $budget = BudgetRequest::findOrFail($id);
        if ($budget->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending requests can be sent to Admin.');
        }
        $budget->update([
            'status' => 'Pending Admin',
            'hr_approved_at' => now(),
        ]);

        AuditTrailService::logUser(
            Auth::user(),
            'budget_request_hr_forwarded',
            'HR forwarded budget request ' . $budget->request_id . ' to Admin for final approval.',
            'budget_request',
            $budget->id
        );
        return redirect()->back()->with('success', 'Request sent to Admin for final approval.');
    }

    /** HR: Reject request with required remarks */
    public function hrReject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $budget = BudgetRequest::findOrFail($id);
        if ($budget->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected by HR.');
        }
        $budget->update([
            'status' => 'Rejected',
            'remarks' => $request->remarks,
        ]);

        AuditTrailService::logUser(
            Auth::user(),
            'budget_request_hr_rejected',
            'HR rejected budget request ' . $budget->request_id . ' with remarks: ' . $request->remarks,
            'budget_request',
            $budget->id
        );
        return redirect()->back()->with('success', 'Request rejected by HR with remarks.');
    }

    /** Admin: Final approve (only when status = Pending Admin) with required remarks */
    public function adminApprove(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $budget = BudgetRequest::findOrFail($id);
        if ($budget->status !== 'Pending Admin') {
            return redirect()->back()->with('error', 'Only requests forwarded by HR can be approved.');
        }

         // Enforce per-employee monthly approval limit (₱50,000)
        if (! $this->canApproveForMonth($budget)) {
            return redirect()
                ->back()
                ->with('error', 'Cannot approve this request: the employee has reached the monthly approved budget limit of ₱50,000.');
        }

        $budget->update([
            'status' => 'Approved',
            'remarks' => $request->remarks,
            'admin_approved_at' => now(),
        ]);
        if (\Schema::hasTable('planning')) {
            \DB::table('planning')->insert([
                'request_id' => $budget->request_id,
                'department' => $budget->department,
                'purpose' => $budget->purpose,
                'amount' => $budget->amount,
                'approved_at' => now(),
            ]);
        }
        AuditTrailService::logUser(
            Auth::user(),
            'budget_request_admin_approved',
            'Admin approved budget request ' . $budget->request_id . ' with remarks: ' . $request->remarks,
            'budget_request',
            $budget->id
        );

        return redirect()->back()->with('success', 'Budget Request approved with remarks. HR and Employee will see the status and remarks.');
    }

    /** Admin: Final reject (only when status = Pending Admin) with required remarks */
    public function adminReject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string',
        ]);

        $budget = BudgetRequest::findOrFail($id);
        if ($budget->status !== 'Pending Admin') {
            return redirect()->back()->with('error', 'Only requests forwarded by HR can be rejected.');
        }
        $budget->update([
            'status' => 'Rejected',
            'remarks' => $request->remarks,
            'admin_approved_at' => now(),
        ]);
        AuditTrailService::logUser(
            Auth::user(),
            'budget_request_admin_rejected',
            'Admin rejected budget request ' . $budget->request_id . ' with remarks: ' . $request->remarks,
            'budget_request',
            $budget->id
        );

        return redirect()->back()->with('success', 'Budget Request rejected with remarks. HR and Employee will see the status and remarks.');
    }

    /**
     * Check if approving the given budget request would exceed the
     * per-employee monthly approved budget limit (₱50,000).
     */
    private function canApproveForMonth(BudgetRequest $budget): bool
    {
        $monthlyLimit = 50000;
        $now = now();

        $approvedTotal = BudgetRequest::where('employee_id', $budget->employee_id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->where('status', 'Approved')
            ->where('id', '!=', $budget->id)
            ->sum('amount');

        return ($approvedTotal + (float) $budget->amount) <= $monthlyLimit;
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