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
                    <table id="payment-table" class="table table-bordered table-hover align-middle">
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

        <div class="card shadow-sm section-card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-semibold mb-0">AI-Style Financial Insight</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="ai-analysis-refresh">
                        Run Analysis Again
                    </button>
                </div>
                <p class="text-muted small mb-2">
                    This analysis is generated locally in your browser based on the payment records in the table above. No external AI API is called.
                </p>
                <div class="border rounded p-3 bg-light" id="ai-analysis-text">
                    Loading analysis based on your current payment data...
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

function collectPaymentStats() {
    const rows = document.querySelectorAll('#payment-table tbody tr');
    let totalDue = 0;
    let totalPaid = 0;
    let paidCount = 0;
    let overdueCount = 0;
    let totalRows = 0;

    rows.forEach(function(row) {
        const cells = row.children;
        if (cells.length < 7) return;

        const amountDueText = cells[3].innerText.replace(/[₱,\s]/g, '');
        const amountPaidText = cells[4].innerText.replace(/[₱,\s]/g, '');
        const statusText = cells[5].innerText.trim();

        const amountDue = parseFloat(amountDueText) || 0;
        const amountPaid = parseFloat(amountPaidText) || 0;

        totalDue += amountDue;
        totalPaid += amountPaid;
        totalRows++;

        if (statusText === 'Paid') paidCount++;
        if (statusText === 'Overdue') overdueCount++;
    });

    const collectionRate = totalDue > 0 ? (totalPaid / totalDue) * 100 : 0;

    return {
        totalDue: totalDue,
        totalPaid: totalPaid,
        collectionRate: collectionRate,
        paidCount: paidCount,
        overdueCount: overdueCount,
        totalRows: totalRows
    };
}

function generateOfflineAiInsight() {
    const stats = collectPaymentStats();
    const target = document.getElementById('ai-analysis-text');
    if (!target) return;

    if (stats.totalRows === 0) {
        target.textContent = 'There are currently no payment records to analyze. Add some payments to see insights generated from your data.';
        return;
    }

    const rateRounded = Math.round(stats.collectionRate);
    const totalDueDisplay = stats.totalDue.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
    const totalPaidDisplay = stats.totalPaid.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });

    const variant = Math.floor(Math.random() * 3);
    let message = '';

    if (variant === 0) {
        message =
            'Based on the current payment records, the system shows a total billed amount of ' + totalDueDisplay +
            ' and total collected payments of ' + totalPaidDisplay + '. Your effective collection rate is around ' +
            rateRounded + '%. This suggests that ' + stats.paidCount + ' invoice(s) are fully paid, while ' +
            stats.overdueCount + ' are tagged as overdue. Maintaining or improving this rate will help stabilize short-term cash flow.';
    } else if (variant === 1) {
        message =
            'Reviewing the latest entries, there are ' + stats.totalRows + ' payment record(s) in the system with an estimated ' +
            rateRounded + '% collection efficiency. Collected payments currently amount to ' + totalPaidDisplay +
            ' versus a total due of ' + totalDueDisplay + '. If you focus on clearing the ' + stats.overdueCount +
            ' overdue item(s), you can unlock additional cash and reduce aging risk on your receivables.';
    } else {
        message =
            'The payment data indicates that clients have already settled ' + totalPaidDisplay +
            ' out of ' + totalDueDisplay + ' billed, resulting in a collection ratio near ' + rateRounded +
            '%. With ' + stats.paidCount + ' record(s) marked as paid and ' + stats.overdueCount +
            ' still overdue, the portfolio is generally ' + (rateRounded >= 80 ? 'healthy' : 'under pressure') +
            '. Consider monitoring overdue accounts more closely to keep your overall performance on track.';
    }

    target.textContent = message;
}

document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.getElementById('ai-analysis-refresh');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', generateOfflineAiInsight);
        // Generate an initial insight when the page loads
        generateOfflineAiInsight();
    }
});
</script>
@endsection
