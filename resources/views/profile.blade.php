@extends('layouts.app')

@section('title', 'Profile - HR Dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <h2 class="h3 mb-4 page-title">Profile</h2>
        <div class="row mt-5 align-items-center">
            <div class="col-md-3 text-center mb-5">
                <div class="avatar avatar-xl">
                    <img src="{{ Auth::user()->photo_path ? asset('storage/' . Auth::user()->photo_path) : asset('assets/avatars/face-1.jpg') }}" alt="Profile Photo" class="avatar-img rounded-circle" onerror="this.onerror=null; this.src='{{ asset('assets/avatars/face-1.jpg') }}';">
                </div>
            </div>
            <div class="col">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h4 class="mb-1">{{ Auth::user()->name }}</h4>
                        <p class="small mb-3"><span class="badge badge-dark">{{ Auth::user()->department }}</span></p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-7">
                        <p class="text-muted">Position: {{ Auth::user()->position }}</p>
                        <p class="text-muted">Email: {{ Auth::user()->email }}</p>
                    </div>
                    <div class="col">
                        <p class="small mb-0 text-muted">Joined: {{ Auth::user()->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- You can add more sections here as needed -->
    </div>
</div>
@endsection
