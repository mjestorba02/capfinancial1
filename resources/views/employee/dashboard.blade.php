<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Finance Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
        }
        .navbar {
            background-color: #007bff !important;
        }
        .navbar-brand, .nav-link {
            color: #fff !important;
        }
        .nav-link.active {
            font-weight: bold;
            text-decoration: underline;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .chart-card { border-radius: 12px; border: none; }
    </style>
</head>
<body>

<!-- 🔹 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="#">Employee Portal</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#analytics-section">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('employee/budget*') ? 'active' : '' }}" href="#budget-section">Budget Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('employee/payments*') ? 'active' : '' }}" href="#payment-section">Payment Portal</a>
                </li>
            </ul>
            <a href="{{ route('employee.logout') }}" class="btn btn-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-5">

    <!-- 🔸 Analytics / Overview Section -->
    <div id="analytics-section" class="mb-5">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0">My Finance Overview</h3>
            <button class="btn btn-light btn-sm" onclick="document.getElementById('budget-section').scrollIntoView({behavior:'smooth'})">Go to Budget</button>
        </div>

        {{-- Summary cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted text-uppercase small mb-2">Total Budget Requested</h6>
                        <h3 class="text-primary mb-0">₱{{ number_format($budgetTotal ?? 0, 2) }}</h3>
                        <small class="text-muted">{{ $requests->count() }} request(s)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted text-uppercase small mb-2">Budget by Status</h6>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge bg-success">Approved: {{ $requests->where('status','Approved')->count() }}</span>
                            <span class="badge bg-secondary">Pending: {{ $requests->where('status','Pending')->count() }}</span>
                            <span class="badge bg-danger">Rejected: {{ $requests->where('status','Rejected')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted text-uppercase small mb-2">Payment Portal Total</h6>
                        <h3 class="text-success mb-0">₱{{ number_format($paymentsTotal ?? 0, 2) }}</h3>
                        <small class="text-muted">{{ $collections->count() }} payment(s)</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts row 1: Budget --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Budget Requests by Status</h5>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartBudgetStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Budget Requested (Last 6 Months)</h5>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartBudgetMonth"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts row 2: Payment portal --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Payments by Status</h5>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartPaymentStatus"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Payments Collected (Last 6 Months)</h5>
                        <div class="position-relative" style="height: 260px;">
                            <canvas id="chartPaymentMonth"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔸 Budget Requests Section -->
    <div id="budget-section" class="mb-5">
        <div class="header d-flex justify-content-between align-items-center">
            <h3 class="m-0">My Budget Requests</h3>
            <button class="btn btn-light btn-sm" onclick="window.scrollTo(0, document.body.scrollHeight)">Go to Payments</button>
        </div>

        <!-- Add Request -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.budget.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <input 
                                type="number" 
                                name="amount" 
                                class="form-control" 
                                required 
                                min="1" 
                                max="5000000" 
                                step="0.01"
                                placeholder="Enter amount (₱)"
                            >
                            <small class="text-muted">Max: ₱5,000,000</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card shadow">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Submitted Requests</h5>
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $req->purpose }}</td>
                            <td>₱{{ number_format($req->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $req->status === 'Approved' ? 'success' : ($req->status === 'Rejected' ? 'danger' : 'secondary') }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td>{{ $req->remarks }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 🔸 Payment Portal Section -->
    <div id="payment-section">
        <div class="header d-flex justify-content-between align-items-center">
            <h3 class="m-0">Payment Portal</h3>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Add Payment</button>
        </div>

        <!-- Payment Table -->
        <div class="card shadow">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">My Payment Records</h5>
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Invoice #</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collections as $col)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $col->customer_name }}</td>
                            <td>{{ $col->invoice_number }}</td>
                            <td>₱{{ number_format($col->amount_due, 2) }}</td>
                            <td>₱{{ number_format($col->amount_paid, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $col->status === 'Paid' ? 'success' : ($col->status === 'Overdue' ? 'danger' : 'secondary') }}">
                                    {{ $col->status }}
                                </span>
                            </td>
                            <td>{{ $col->payment_date }}</td>
                            <td>{{ $col->remarks }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- 🔹 Add Payment Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('employee.payment.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addModalLabel">Add Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <!-- Customer Name -->
                <div class="form-group mb-3">
                    <label class="fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name"
                        class="form-control"
                        value="{{ $employee->name }}">
                </div>

                <!-- Amount Paid -->
                <div class="form-group mb-3">
                    <label class="fw-semibold">Payment</label>
                    <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" required>
                </div>

                <!-- Hidden Amount Due (auto set to match amount_paid) -->
                <input type="hidden" name="amount_due" id="amount_due" value="0">

                <!-- Hidden Employee ID (from session) -->
                <input type="hidden" name="employee_id" value="{{ Session::get('employee_id') }}">

                <!-- Hidden Default Fields -->
                <input type="hidden" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                <input type="hidden" name="status" value="Pending">

                <!-- Remarks -->
                <div class="form-group mb-3">
                    <label class="fw-semibold">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.getElementById('amount_paid').addEventListener('input', function() {
    document.getElementById('amount_due').value = this.value || 0;
});

(function() {
    var budgetStatus = @json($budgetByStatus ?? ['labels'=>[],'amounts'=>[]]);
    var budgetMonth = @json($budgetByMonth ?? collect());
    var paymentStatus = @json($paymentsByStatus ?? ['labels'=>[],'amounts'=>[]]);
    var paymentMonth = @json($paymentsByMonth ?? collect());

    var colors = { green: 'rgba(40, 167, 69, 0.8)', gray: 'rgba(108, 117, 125, 0.8)', red: 'rgba(220, 53, 69, 0.8)', blue: 'rgba(0, 123, 255, 0.8)' };
    var borderColors = ['#28a745', '#6c757d', '#dc3545'];

    if (document.getElementById('chartBudgetStatus') && budgetStatus.labels.length) {
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

    if (document.getElementById('chartPaymentStatus') && paymentStatus.labels.length) {
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
</body>
</html>