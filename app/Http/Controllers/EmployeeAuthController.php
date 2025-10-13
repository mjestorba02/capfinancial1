<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class EmployeeAuthController extends Controller
{
    // 🔹 Show login form
    public function showLoginForm()
    {
        return view('employee.login');
    }

    // 🔹 Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find employee by email
        $employee = Employee::where('email', $request->email)->first();

        // 🔹 If employee doesn't exist or password doesn't match
        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return back()->withErrors(['login' => 'Invalid email or password.']);
        }

        // 🔹 Store employee info in session
        Session::put('employee_id', $employee->id);
        Session::put('employee_name', $employee->name);
        Session::put('employee_department', $employee->department);

        // 🔹 Redirect to employee dashboard
        return redirect()->route('employee.dashboard')
            ->with('success', 'Welcome back, ' . $employee->name . '!');
    }

    // 🔹 Handle logout
    public function logout()
    {
        Session::forget(['employee_id', 'employee_name', 'employee_department']);

        return redirect()->route('employee.login')->with('success', 'You have been logged out.');
    }
}