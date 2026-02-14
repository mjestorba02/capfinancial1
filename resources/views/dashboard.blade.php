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

        {{-- Analytics / Charts --}}
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">Financial Analytics</h5>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Cash Flow Breakdown</h6>
                        <div class="position-relative" style="height: 240px;">
                            <canvas id="chartFlowBreakdown"></canvas>
                        </div>
                        <small class="text-muted">Collections vs Payables vs Disbursements</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Budget Requests by Status</h6>
                        <div class="position-relative" style="height: 240px;">
                            <canvas id="chartBudgetStatus"></canvas>
                        </div>
                        <small class="text-muted">Amount by Approved / Pending / Rejected</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Monthly Trend (Last 6 Months)</h6>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartMonthlyTrend"></canvas>
                        </div>
                        <small class="text-muted">Collections, Disbursements &amp; Budget Request Amount</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Budget Allocation by Department</h6>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartAllocation"></canvas>
                        </div>
                        <small class="text-muted">Allocated vs Used</small>
                    </div>
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
                                <td>{{ $payable->invoice_number ?? $payable->payment_id }}</td>
                                <td>{{ $payable->vendor ?? '—' }}</td>
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
                            ? round(($allocation->used / $allocation->allocated) * 100, 2)
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
                        <small>₱{{ number_format($allocation->used, 2) }} of ₱{{ number_format($allocation->allocated, 2) }}</small>
                    </div>
                @empty
                    <p class="text-muted">No budget allocation data available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    var flowBreakdown = @json($flowBreakdown ?? ['labels'=>[],'amounts'=>[]]);
    var budgetByStatus = @json($budgetByStatus ?? ['labels'=>[],'amounts'=>[]]);
    var monthlyData = @json($monthlyData ?? []);
    var allocationChart = @json($allocationChart ?? []);

    var hasFlow = flowBreakdown.amounts && flowBreakdown.amounts.some(function(v){ return v > 0; });
    if (document.getElementById('chartFlowBreakdown') && hasFlow) {
        new Chart(document.getElementById('chartFlowBreakdown'), {
            type: 'doughnut',
            data: {
                labels: flowBreakdown.labels,
                datasets: [{
                    data: flowBreakdown.amounts,
                    backgroundColor: ['rgba(0, 123, 255, 0.8)', 'rgba(220, 53, 69, 0.8)', 'rgba(40, 167, 69, 0.8)'],
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    var hasBudget = budgetByStatus.amounts && budgetByStatus.amounts.some(function(v){ return v > 0; });
    if (document.getElementById('chartBudgetStatus') && (hasBudget || budgetByStatus.amounts.length)) {
        new Chart(document.getElementById('chartBudgetStatus'), {
            type: 'doughnut',
            data: {
                labels: budgetByStatus.labels,
                datasets: [{
                    data: budgetByStatus.amounts,
                    backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(255, 193, 7, 0.8)', 'rgba(220, 53, 69, 0.8)'],
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    if (document.getElementById('chartMonthlyTrend') && monthlyData.length) {
        new Chart(document.getElementById('chartMonthlyTrend'), {
            type: 'line',
            data: {
                labels: monthlyData.map(function(m){ return m.label; }),
                datasets: [
                    { label: 'Collections (₱)', data: monthlyData.map(function(m){ return m.collections; }), borderColor: '#007bff', backgroundColor: 'rgba(0, 123, 255, 0.1)', fill: true, tension: 0.3 },
                    { label: 'Disbursements (₱)', data: monthlyData.map(function(m){ return m.disbursements; }), borderColor: '#28a745', backgroundColor: 'rgba(40, 167, 69, 0.1)', fill: true, tension: 0.3 },
                    { label: 'Budget Requested (₱)', data: monthlyData.map(function(m){ return m.budget_amount; }), borderColor: '#ffc107', backgroundColor: 'rgba(255, 193, 7, 0.1)', fill: true, tension: 0.3 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } }
        });
    }

    if (document.getElementById('chartAllocation') && allocationChart.length) {
        new Chart(document.getElementById('chartAllocation'), {
            type: 'bar',
            data: {
                labels: allocationChart.map(function(a){ return a.department; }),
                datasets: [
                    { label: 'Allocated (₱)', data: allocationChart.map(function(a){ return a.allocated; }), backgroundColor: 'rgba(0, 123, 255, 0.7)' },
                    { label: 'Used (₱)', data: allocationChart.map(function(a){ return a.used; }), backgroundColor: 'rgba(40, 167, 69, 0.7)' }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false }, y: { beginAtZero: true, stacked: false } }, plugins: { legend: { position: 'bottom' } } }
        });
    }
})();
</script>
@endsection