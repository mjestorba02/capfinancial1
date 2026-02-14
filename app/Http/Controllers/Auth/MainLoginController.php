<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainLoginController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Handle admin login: validate credentials, send OTP, redirect to OTP page.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::guard('web')->validate([
            'email' => $request->email,
            'password' => $request->password,
        ])) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Invalid email or password.');
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        $this->otpService->createAndSend(
            OtpService::TYPE_ADMIN,
            (int) $user->id,
            $user->email
        );

        $request->session()->put('otp_type', OtpService::TYPE_ADMIN);
        $request->session()->put('otp_verifiable_id', $user->id);
        $request->session()->put('otp_remember', $request->boolean('remember'));

        return redirect()->route('login.otp.form');
    }

    /**
     * Show OTP verification form (admin).
     */
    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('otp_verifiable_id') || $request->session()->get('otp_type') !== OtpService::TYPE_ADMIN) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }
        return view('auth.otp-verify', ['context' => 'admin']);
    }

    /**
     * Verify OTP and log in admin.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $type = $request->session()->get('otp_type');
        $verifiableId = $request->session()->get('otp_verifiable_id');
        $remember = $request->session()->get('otp_remember', false);

        if ($type !== OtpService::TYPE_ADMIN || !$verifiableId) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        if (!$this->otpService->verify($type, (int) $verifiableId, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        $user = User::find($verifiableId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $request->session()->forget(['otp_type', 'otp_verifiable_id', 'otp_remember']);
        Auth::guard('web')->login($user, $remember);

        return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
    }
}
