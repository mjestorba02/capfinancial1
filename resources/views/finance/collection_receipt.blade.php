@extends('layouts.app')

@section('title', 'Collection Receipt')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Collection Receipt</h5>
                        <small class="text-muted">Details for this payment collection.</small>
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
                            <p class="mb-0">{{ $collection->payment_date ?? '-' }}</p>
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
                                        @php
                                            $badgeClass = match($collection->status) {
                                                'Paid' => 'success',
                                                'Ordered' => 'info',
                                                'Overdue' => 'danger',
                                                'Pending' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $collection->status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted small mb-0">
                        This is a system-generated receipt view for this collection record.
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('collections.index') }}" class="btn btn-secondary btn-sm">Back to Collections</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

