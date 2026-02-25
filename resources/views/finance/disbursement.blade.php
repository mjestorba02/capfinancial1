@extends('layouts.app')

@section('title', 'Disbursements')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Finance - Disbursements</h2>
            <p class="text-muted mb-0">Manage and track company disbursement transactions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Disbursement Table --}}
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">Disbursement Summary</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voucher No</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disbursements as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->voucher_no }}</td>
                            <td>{{ $item->vendor }}</td>
                            <td>{{ $item->category }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($item->status) {
                                        'Released' => 'success',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">{{ $item->status }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->disbursement_date)->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i>No disbursements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection