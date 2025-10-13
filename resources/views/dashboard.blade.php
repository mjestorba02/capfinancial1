@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Financial Dashboard</h4>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Collections</h6>
                    <h3 class="text-primary">₱{{ number_format($totalCollections, 2) }}</h3>
                    <small>{{ $collectionsProgress }}% of total flow</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Budget Requests</h6>
                    <h3 class="text-warning">{{ $budgetRequests }}</h3>
                    <small>Active Requests</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Payables</h6>
                    <h3 class="text-danger">₱{{ number_format($totalPayables, 2) }}</h3>
                    <small>{{ $payablesProgress }}% of total flow</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Disbursements</h6>
                    <h3 class="text-success">₱{{ number_format($totalDisbursements, 2) }}</h3>
                    <small>{{ $disbursementProgress }}% of total flow</small>
                </div>
            </div>
        </div>

        {{-- Budget Requests Snapshot --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="mb-3">Recent Budget Requests</h5>
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBudgetRequests as $req)
                            <tr>
                                <td>{{ $req->request_id }}</td>
                                <td>{{ $req->employee->name ?? 'N/A' }}</td>
                                <td>{{ $req->department }}</td>
                                <td>{{ $req->purpose }}</td>
                                <td>₱{{ number_format($req->amount, 2) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($req->status) {
                                            'Pending' => 'warning',
                                            'Rejected' => 'danger',
                                            default => 'success',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $req->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No recent requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('budget_requests.index') }}" class="btn btn-primary btn-sm">View All Requests</a>
            </div>
        </div>

        {{-- Payables Snapshot --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="mb-3">Outstanding Payables</h5>
                <table class="table table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Supplier</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayables as $payable)
                            <tr>
                                <td>{{ $payable->reference_no }}</td>
                                <td>{{ $payable->supplier }}</td>
                                <td>{{ $payable->due_date }}</td>
                                <td>₱{{ number_format($payable->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payable->status === 'Paid' ? 'success' : 'danger' }}">
                                        {{ $payable->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No payables found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <a href="{{ route('accounts.index') }}" class="btn btn-primary btn-sm">View All Payables</a>
            </div>
        </div>

        {{-- Budget Allocation Progress --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <h5 class="mb-3">Budget Allocation by Department</h5>
                @forelse($budgetAllocations as $allocation)
                    @php
                        $percentage = $allocation->allocated > 0 
                            ? round(($allocation->spent / $allocation->allocated) * 100, 2)
                            : 0;
                    @endphp
                    <div class="mb-2">
                        <strong>{{ $allocation->department }}</strong>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" 
                                role="progressbar"
                                style="width: {{ $percentage }}%"
                                aria-valuenow="{{ $percentage }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                                {{ $percentage }}%
                            </div>
                        </div>
                        <small>₱{{ number_format($allocation->spent, 2) }} of ₱{{ number_format($allocation->allocated, 2) }}</small>
                    </div>
                @empty
                    <p class="text-muted">No budget allocation data available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection