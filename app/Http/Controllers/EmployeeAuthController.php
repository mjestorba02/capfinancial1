<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class EmployeeAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    // 🔹 Show login form
    public function showLoginForm()
    {
        return view('employee.login');
    }

    // 🔹 Handle login: validate credentials, send OTP, redirect to OTP page
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return back()->withErrors(['login' => 'Invalid email or password.']);
        }

        if ($employee->approval_status !== 'approved') {
            return back()->withErrors(['login' => 'Your account is still pending approval. Please wait for an admin to approve your registration.']);
        }

        $this->otpService->createAndSend(
            OtpService::TYPE_EMPLOYEE,
            (int) $employee->id,
            $employee->email
        );

        $request->session()->put('otp_type', OtpService::TYPE_EMPLOYEE);
        $request->session()->put('otp_verifiable_id', $employee->id);

        return redirect()->route('employee.login.otp.form');
    }

    // 🔹 Show OTP verification form (employee)
    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('otp_verifiable_id') || $request->session()->get('otp_type') !== OtpService::TYPE_EMPLOYEE) {
            return redirect()->route('employee.login')->withErrors(['login' => 'Please log in first.']);
        }
        return view('auth.otp-verify', ['context' => 'employee']);
    }

    // 🔹 Verify OTP and complete employee login
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $type = $request->session()->get('otp_type');
        $verifiableId = $request->session()->get('otp_verifiable_id');

        if ($type !== OtpService::TYPE_EMPLOYEE || !$verifiableId) {
            return redirect()->route('employee.login')->withErrors(['login' => 'Session expired. Please log in again.']);
        }

        if (!$this->otpService->verify($type, (int) $verifiableId, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        $employee = Employee::find($verifiableId);
        if (!$employee) {
            return redirect()->route('employee.login')->withErrors(['login' => 'Account not found.']);
        }

        $request->session()->forget(['otp_type', 'otp_verifiable_id']);
        Session::put('employee_id', $employee->id);
        Session::put('employee_name', $employee->name);
        Session::put('employee_department', $employee->department);

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