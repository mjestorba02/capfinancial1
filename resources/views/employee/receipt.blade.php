@extends('layouts.employee')

@section('title', 'Receipt ' . $order->receipt_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center border-bottom pb-3 mb-3">
                    <h4 class="mb-1">Financial Management System</h4>
                    <p class="text-muted small mb-0">Transaction Receipt</p>
                </div>
                <h5 class="fw-bold mb-3">Receipt # {{ $order->receipt_number }}</h5>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted">Date</td>
                        <td>{{ $order->created_at->format('F d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Budget Request</td>
                        <td>{{ $order->budgetRequest->request_id ?? '—' }} ({{ $order->budgetRequest->purpose ?? '—' }})</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Employee</td>
                        <td>{{ $order->employee->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Material / Description</td>
                        <td>{{ $order->material_description }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount</td>
                        <td class="fw-bold">₱{{ number_format($order->amount, 2) }}</td>
                    </tr>
                    @if($order->remarks)
                    <tr>
                        <td class="text-muted">Remarks</td>
                        <td>{{ $order->remarks }}</td>
                    </tr>
                    @endif
                </table>
                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('employee.budget.receipt.pdf', $order->id) }}" class="btn btn-primary">Download PDF</a>
                    <a href="{{ route('employee.budget') }}" class="btn btn-outline-secondary">Back to Budget</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
