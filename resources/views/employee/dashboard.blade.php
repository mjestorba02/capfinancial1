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
        <form method="POST" action="{{ route('employee.budget.store') }}" class="modal-content">
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
                        value="{{ $employee->name }}"
                        readonly>
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

<script>
document.getElementById('amount_paid').addEventListener('input', function() {
    document.getElementById('amount_due').value = this.value || 0;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>