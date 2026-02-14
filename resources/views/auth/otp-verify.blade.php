@extends('layouts.auth')

@section('title', ($context === 'employee' ? 'Employee' : 'Admin') . ' - Verify OTP')

@section('content')
<div class="wrapper vh-100 bg-light">
    <div class="row align-items-center h-100">
        <form method="POST" action="{{ $context === 'employee' ? route('employee.login.otp.verify') : route('login.otp.verify') }}" class="col-lg-3 col-md-4 col-10 mx-auto text-center p-4 shadow rounded bg-white">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger text-left">
                    {{ session('error') }}
                </div>
            @endif
            @error('otp')
                <div class="alert alert-danger text-left">
                    {{ $message }}
                </div>
            @enderror
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="#">
                <svg version="1.1" id="logo" class="navbar-brand-img brand-md" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120"
                    xml:space="preserve" style="height:60px;">
                    <g>
                        <polygon class="st0" points="78,105 15,105 24,87 87,87" />
                        <polygon class="st0" points="96,69 33,69 42,51 105,51" />
                        <polygon class="st0" points="78,33 15,33 24,15 87,15" />
                    </g>
                </svg>
            </a>
            <h1 class="h6 mb-3">Two-factor verification</h1>
            <p class="text-muted small mb-3">We sent a 6-digit code to your email. Enter it below.</p>
            <div class="form-group mb-3">
                <label for="otp" class="sr-only">Verification code</label>
                <input type="text" name="otp" id="otp" class="form-control form-control-lg text-center" placeholder="000000" maxlength="6" pattern="[0-9]*" inputmode="numeric" required autofocus>
            </div>
            <button class="btn btn-lg btn-primary btn-block w-100" type="submit">Verify</button>
            <div class="mt-3">
                <a href="{{ $context === 'employee' ? route('employee.login') : route('login') }}" class="text-primary">Back to login</a>
            </div>
            <p class="mt-5 mb-3 text-muted">© 2025</p>
        </form>
    </div>
</div>
@endsection
