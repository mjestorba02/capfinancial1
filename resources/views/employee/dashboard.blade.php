@extends('layouts.employee')

@section('title', 'Employee Finance Portal')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Employee Finance Dashboard</h4>
        <p class="page-subtitle text-muted d-none d-md-block">Overview of your budget requests, payments, and quick actions.</p>

        <!-- Analytics / Overview Section -->
        <div id="analytics-section" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">My Finance Overview</h5>
                <div>
                    <a href="{{ route('employee.budget.requests') }}" class="btn btn-primary btn-sm me-2">Budget Requests</a>
                    <a href="{{ route('employee.budget') }}" class="btn btn-outline-primary btn-sm me-2">Budget</a>
                    <a href="{{ route('employee.payment.portal') }}" class="btn btn-outline-primary btn-sm">Payment Portal</a>
                </div>
            </div>

            {{-- Summary cards (same style as admin dashboard) --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3 h-100">
                        <h6 class="text-muted text-uppercase small mb-2">Total Budget Requested</h6>
                        <h3 class="text-primary mb-0">₱{{ number_format($budgetTotal ?? 0, 2) }}</h3>
                        <small class="text-muted">{{ $requests->count() }} request(s)</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3 h-100">
                        <h6 class="text-muted text-uppercase small mb-2">Budget by Status</h6>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge bg-success">Approved: {{ $requests->where('status','Approved')->count() }}</span>
                            <span class="badge bg-secondary">Pending: {{ $requests->where('status','Pending')->count() }}</span>
                            <span class="badge bg-danger">Rejected: {{ $requests->where('status','Rejected')->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-3 h-100">
                        <h6 class="text-muted text-uppercase small mb-2">Payment Portal Total</h6>
                        <h3 class="text-success mb-0">₱{{ number_format($paymentsTotal ?? 0, 2) }}</h3>
                        <small class="text-muted">{{ $collections->count() }} payment(s)</small>
                    </div>
                </div>
            </div>

            {{-- Charts row 1: Budget --}}
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3">Financial Analytics</h5>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Budget Requests by Status</h6>
                            <div class="position-relative" style="height: 260px;">
                                <canvas id="chartBudgetStatus"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Budget Requested (Last 6 Months)</h6>
                            <div class="position-relative" style="height: 260px;">
                                <canvas id="chartBudgetMonth"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Payments by Status</h6>
                            <div class="position-relative" style="height: 260px;">
                                <canvas id="chartPaymentStatus"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Payments Collected (Last 6 Months)</h6>
                            <div class="position-relative" style="height: 260px;">
                                <canvas id="chartPaymentMonth"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    var budgetStatus = @json($budgetByStatus ?? ['labels'=>[],'amounts'=>[]]);
    var budgetMonth = @json($budgetByMonth ?? collect());
    var paymentStatus = @json($paymentsByStatus ?? ['labels'=>[],'amounts'=>[]]);
    var paymentMonth = @json($paymentsByMonth ?? collect());

    var colors = { green: 'rgba(40, 167, 69, 0.8)', gray: 'rgba(108, 117, 125, 0.8)', red: 'rgba(220, 53, 69, 0.8)', blue: 'rgba(0, 123, 255, 0.8)' };

    if (document.getElementById('chartBudgetStatus') && budgetStatus.labels && budgetStatus.labels.length) {
        new Chart(document.getElementById('chartBudgetStatus'), {
            type: 'doughnut',
            data: {
                labels: budgetStatus.labels,
                datasets: [{ data: budgetStatus.amounts, backgroundColor: [colors.green, colors.gray, colors.red], borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
    if (document.getElementById('chartBudgetMonth') && budgetMonth.length) {
        new Chart(document.getElementById('chartBudgetMonth'), {
            type: 'bar',
            data: {
                labels: budgetMonth.map(function(m){ return m.label; }),
                datasets: [{ label: 'Amount (₱)', data: budgetMonth.map(function(m){ return m.amount; }), backgroundColor: colors.blue }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
        });
    }
    if (document.getElementById('chartPaymentStatus') && paymentStatus.labels && paymentStatus.labels.length) {
        new Chart(document.getElementById('chartPaymentStatus'), {
            type: 'doughnut',
            data: {
                labels: paymentStatus.labels,
                datasets: [{ data: paymentStatus.amounts, backgroundColor: [colors.green, colors.gray, colors.red], borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
    if (document.getElementById('chartPaymentMonth') && paymentMonth.length) {
        new Chart(document.getElementById('chartPaymentMonth'), {
            type: 'line',
            data: {
                labels: paymentMonth.map(function(m){ return m.label; }),
                datasets: [{ label: 'Collected (₱)', data: paymentMonth.map(function(m){ return m.amount; }), borderColor: '#28a745', backgroundColor: 'rgba(40, 167, 69, 0.1)', fill: true, tension: 0.3 }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    }
})();
</script>
@endsection
