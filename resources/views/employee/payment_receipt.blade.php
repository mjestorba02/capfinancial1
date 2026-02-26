@extends('layouts.employee')

@section('title', 'Payment Receipt')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Payment Receipt</h5>
                    <small class="text-muted">Receipt for your recorded payment.</small>
                </div>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex justify-content-between">
                    <div>
                        <h6 class="fw-semibold mb-1">Customer</h6>
                        <p class="mb-0">{{ $collection->customer_name }}</p>
                    </div>
                    <div class="text-end">
                        <h6 class="fw-semibold mb-1">Invoice / Receipt No.</h6>
                        <p class="mb-0">{{ $collection->invoice_number }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-1">Department</h6>
                        <p class="mb-0">{{ $collection->remarks ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-1">Payment Date</h6>
                        <p class="mb-0">{{ $collection->payment_date ?? now()->format('Y-m-d') }}</p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">Amount Due</th>
                                <td>₱{{ number_format($collection->amount_due, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Amount Paid</th>
                                <td>₱{{ number_format($collection->amount_paid, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-{{ $collection->status === 'Paid' ? 'success' : ($collection->status === 'Overdue' ? 'danger' : 'secondary') }}">
                                        {{ $collection->status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-muted small mb-0">
                    This is a system-generated receipt for your payment record.
                </p>
            </div>
            
        </div>
    </div>
</div>
@endsection

