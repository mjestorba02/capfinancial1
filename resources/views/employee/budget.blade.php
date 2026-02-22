@extends('layouts.employee')

@section('title', 'Budget')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Budget</h4>
        <p class="text-muted mb-4">Use your approved budget requests to order materials. Each order creates a receipt and appears in Accounts Receivable - Collections (admin/HR) as <strong>Ordered</strong>.</p>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Approved budget requests (available to order from) --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Approved Budget Requests</h5>
                <p class="text-muted small mb-3">Only requests approved by both HR and Admin appear here. You can place material orders against each approved budget.</p>
                @if($approvedRequests->isEmpty())
                    <p class="text-muted mb-0">No approved budget requests yet. Submit a request in <a href="{{ route('employee.budget.requests') }}">Budget Requests</a> and wait for HR and Admin approval.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Request ID</th>
                                    <th>Purpose</th>
                                    <th>Approved Amount</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedRequests as $req)
                                @php
                                    $used = $req->budgetOrders->sum('amount');
                                    $remaining = $req->amount - $used;
                                @endphp
                                <tr>
                                    <td><strong>{{ $req->request_id }}</strong></td>
                                    <td>{{ $req->purpose }}</td>
                                    <td>₱{{ number_format($req->amount, 2) }}</td>
                                    <td>₱{{ number_format($used, 2) }}</td>
                                    <td>₱{{ number_format($remaining, 2) }}</td>
                                    <td>
                                        @if($remaining > 0)
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal{{ $req->id }}">
                                                Order Material
                                            </button>
                                        @else
                                            <span class="text-muted small">Budget fully used</span>
                                        @endif
                                    </td>
                                </tr>
                                {{-- Order modal per request --}}
                                <div class="modal fade" id="orderModal{{ $req->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="POST" action="{{ route('employee.budget.order.store') }}" class="modal-content">
                                            @csrf
                                            <input type="hidden" name="budget_request_id" value="{{ $req->id }}">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">Order Material — {{ $req->request_id }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="small text-muted">Remaining budget: ₱{{ number_format($remaining, 2) }}</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Material / Item Description <span class="text-danger">*</span></label>
                                                    <input type="text" name="material_description" class="form-control" required placeholder="e.g. Office supplies, cables">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                                                    <input type="number" name="amount" class="form-control" required min="0.01" step="0.01" max="{{ $remaining }}" placeholder="0.00">
                                                    <small class="text-muted">Max: ₱{{ number_format($remaining, 2) }}</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Place Order & Create Receipt</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- My orders and receipts --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">My Orders & Receipts</h5>
                @if($orders->isEmpty())
                    <p class="text-muted mb-0">No orders yet. Place an order from an approved budget above.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Budget Request</th>
                                    <th>Material</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td><strong>{{ $order->receipt_number }}</strong></td>
                                    <td>{{ $order->budgetRequest->request_id ?? '—' }}</td>
                                    <td>{{ Str::limit($order->material_description, 40) }}</td>
                                    <td>₱{{ number_format($order->amount, 2) }}</td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('employee.budget.receipt', $order->id) }}" class="btn btn-outline-secondary btn-sm me-1" target="_blank">View</a>
                                        <a href="{{ route('employee.budget.receipt.pdf', $order->id) }}" class="btn btn-outline-primary btn-sm">Download PDF</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
