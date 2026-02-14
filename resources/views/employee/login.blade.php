@extends('layouts.auth')

@section('title', 'Employee Login')

@section('content')
<div class="wrapper vh-100 bg-light">
    <div class="row align-items-center h-100">
        <form method="POST" action="{{ route('employee.login.post') }}" class="col-lg-3 col-md-4 col-10 mx-auto text-center p-4 shadow rounded bg-white">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger text-left">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success text-left">
                    {{ session('success') }}
                </div>
            @endif
            @error('login')
                <div class="alert alert-danger text-left">
                    {{ $message }}
                </div>
            @enderror
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="#">
                <svg version="1.1" id="logo" class="navbar-brand-img brand-md" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 120 120"
                    xml:space="preserve" style="height:60px;">
                    <g>
                        <polygon fill="#1b68ff" points="78,105 15,105 24,87 87,87" />
                        <polygon fill="#1b68ff" points="96,69 33,69 42,51 105,51" />
                        <polygon fill="#1b68ff" points="78,33 15,33 24,15 87,15" />
                    </g>
                </svg>
            </a>
            <h1 class="h6 mb-3">Employee Login</h1>
            <div class="form-group mb-3">
                <label for="inputEmail" class="sr-only">Email address</label>
                <input type="email" name="email" id="inputEmail" class="form-control form-control-lg" placeholder="Email address" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group mb-3">
                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" name="password" id="inputPassword" class="form-control form-control-lg" placeholder="Password" required>
            </div>
            <div class="form-group mb-3 text-left">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Stay logged in</label>
            </div>
            <button class="btn btn-lg btn-primary btn-block w-100" type="submit">Let me in</button>
            <p class="mt-5 mb-3 text-muted">© 2025</p>
        </form>
    </div>
</div>
@endsection
