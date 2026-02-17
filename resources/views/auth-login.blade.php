@extends('layouts.auth')

@section('title', 'Login - Dashboard')

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
                <div class="input-group input-group-lg">
                    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                        <span class="fe fe-eye"></span>
                    </button>
                </div>
            </div>
            <div class="form-group mb-3 text-left">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Stay logged in</label>
            </div>
            <button class="btn btn-lg btn-primary btn-block w-100" type="submit">Sign In</button>
            <div class="mt-3">
                <a href="{{ route('register') }}" class="text-primary">Create an account</a>
            </div>
            <p class="mt-5 mb-3 text-muted">Copyright © 2026</p>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('togglePassword');
        var input = document.getElementById('inputPassword');
        if (toggle && input) {
            toggle.addEventListener('click', function () {
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                this.innerHTML = isPassword
                    ? '<span class="fe fe-eye-off"></span>'
                    : '<span class="fe fe-eye"></span>';
            });
        }
    });
</script>
@endsection
