<?php

namespace App\Http\Controllers;

use App\Mail\AccountApprovedMail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserApprovalController extends Controller
{
    /**
     * Display pending user and employee registrations for admin approval.
     */
    public function index()
    {
        $pendingUsers = User::where('approval_status', 'pending')->orderBy('created_at', 'desc')->get();
        $pendingEmployees = Employee::where('approval_status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('user-approvals.index', compact('pendingUsers', 'pendingEmployees'));
    }

    /**
     * Approve a pending user.
     */
    public function approve(User $user)
    {
        if ($user->approval_status !== 'pending') {
            return redirect()->route('user-approvals.index')
                ->with('error', 'This user has already been processed.');
        }

        $user->update(['approval_status' => 'approved']);

        Mail::to($user->email)->send(new AccountApprovedMail(
            $user->name,
            url(route('login')),
            'user'
        ));

        return redirect()->route('user-approvals.index')
            ->with('success', $user->name . '\'s account has been approved. They have been notified by email and can now log in.');
    }

    /**
     * Reject a pending user.
     */
    public function reject(User $user)
    {
        if ($user->approval_status !== 'pending') {
            return redirect()->route('user-approvals.index')
                ->with('error', 'This user has already been processed.');
        }

        $user->update(['approval_status' => 'rejected']);

        return redirect()->route('user-approvals.index')
            ->with('success', $user->name . '\'s account has been rejected.');
    }

    /**
     * Approve a pending employee.
     */
    public function approveEmployee(Employee $employee)
    {
        if ($employee->approval_status !== 'pending') {
            return redirect()->route('user-approvals.index')
                ->with('error', 'This employee has already been processed.');
        }

        $employee->update(['approval_status' => 'approved']);

        Mail::to($employee->email)->send(new AccountApprovedMail(
            $employee->name,
            url(route('employee.login')),
            'employee'
        ));

        return redirect()->route('user-approvals.index')
            ->with('success', $employee->name . '\'s account has been approved. They have been notified by email and can now log in to the employee portal.');
    }

    /**
     * Reject a pending employee.
     */
    public function rejectEmployee(Employee $employee)
    {
        if ($employee->approval_status !== 'pending') {
            return redirect()->route('user-approvals.index')
                ->with('error', 'This employee has already been processed.');
        }

        $employee->update(['approval_status' => 'rejected']);

        return redirect()->route('user-approvals.index')
            ->with('success', $employee->name . '\'s account has been rejected.');
    }
}
