
@extends('layouts.auth')

@section('title', 'Register - HR Dashboard')

@section('content')
<div class="wrapper vh-100 bg-light">
    <div class="row align-items-center h-100">
        <form method="POST" action="{{ route('register') }}" class="col-lg-4 col-md-6 col-10 mx-auto text-center p-4 shadow rounded bg-white">
            @csrf
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
            <h1 class="h6 mb-3">Create Account</h1>
            <div class="form-group mb-3">
                <label for="name" class="sr-only">Full Name</label>
                <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="Full Name" required autofocus>
            </div>
            <div class="form-group mb-3">
                <label for="email" class="sr-only">Email address</label>
                <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Email address" required>
            </div>
            <div class="form-group mb-3">
                <label for="position" class="sr-only">Position</label>
                <select name="position" id="position" class="form-control form-control-lg" required>
                    <option value="">Select Position</option>
                    <option value="Employee">Employee</option>
                    <option value="Admin">Admin</option>
                    <option value="HR">HR</option>
                </select>
            </div>
          
            <div class="form-group mb-3">
                <label for="password" class="sr-only">Password</label>
                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Password" required>
            </div>
            <div class="form-group mb-3">
                <label for="password_confirmation" class="sr-only">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg" placeholder="Confirm Password" required>
            </div>
            <button class="btn btn-lg btn-success btn-block w-100" type="submit">Register</button>
            <div class="mt-3">
                <a href="{{ route('login') }}" class="text-primary">Already have an account?</a>
            </div>
            <p class="mt-5 mb-3 text-muted">© 2025</p>
        </form>
    </div>
</div>
@endsection
