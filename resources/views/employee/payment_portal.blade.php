@extends('layouts.employee')

@section('title', 'Payment Portal')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0">Payment Portal</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">Add Payment</button>
        </div>
        <p class="page-subtitle text-muted">View and add payment records linked to invoices.</p>

        <div class="card shadow-sm section-card">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">My Payment Records</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Invoice #</th>
                                <th>Amount Due</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Department</th>
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
                                    <span class="badge bg-{{ $col->status === 'Paid' ? 'success' : ($col->status === 'Overdue' ? 'danger' : 'secondary') }}">{{ $col->status }}</span>
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
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('employee.payment.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="addModalLabel">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ $employee->name }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment</label>
                    <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" required>
                </div>
                <input type="hidden" name="amount_due" id="amount_due" value="0">
                <input type="hidden" name="employee_id" value="{{ Session::get('employee_id') }}">
                <input type="hidden" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                <input type="hidden" name="status" value="Paid">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="remarks" class="form-control">
                        <option value="">-- Select Department --</option>
                        <option value="IT / Technical Department">IT / Technical Department</option>
                        <option value="Marketing Department">Marketing Department</option>
                        <option value="Logistics / Operations Department">Logistics / Operations Department</option>
                        <option value="Sales Department">Sales Department</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@endsection

@section('scripts')
<script>
document.getElementById('amount_paid').addEventListener('input', function() {
    document.getElementById('amount_due').value = this.value || 0;
});
</script>
@endsection
