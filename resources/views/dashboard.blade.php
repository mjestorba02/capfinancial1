@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">HR Dashboard</h4>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush