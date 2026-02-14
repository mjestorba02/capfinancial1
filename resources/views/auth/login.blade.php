@extends('layouts.auth')

@section('title', 'Login - HR Dashboard')

@section('content')
<div class="wrapper vh-100 bg-light">
    <div class="row align-items-center h-100">
        <form method="POST" action="{{ route('login') }}" class="col-lg-3 col-md-4 col-10 mx-auto text-center p-4 shadow rounded bg-white">
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
            @if(session('pending_approval'))
                <div class="alert alert-info text-left d-flex align-items-center" role="alert">
                    <i class="fe fe-clock me-2" style="font-size: 1.25rem;"></i>
                    <div>
                        <strong>Registration complete.</strong> Please wait for approval. An admin will review your account before you can sign in.
                    </div>
                </div>
            @endif
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
            <h1 class="h6 mb-3">Sign in</h1>
            <div class="form-group mb-3">
                <label for="inputEmail" class="sr-only">Email address</label>
                <input type="email" name="email" id="inputEmail" class="form-control form-control-lg" placeholder="Email address" required autofocus>
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
            <div class="mt-3">
                <a href="{{ route('register') }}" class="text-primary">Create an account</a>
            </div>
            <p class="mt-5 mb-3 text-muted">© 2025</p>
        </form>
    </div>
</div>
@endsection
