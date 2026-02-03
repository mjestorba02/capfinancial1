@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="container-fluid px-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Accounts Management</h2>
            <p class="text-muted mb-0">Manage Accounts Payable and view Paid Receivables from Collections.</p>
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    {{-- ============================= --}}
    {{-- ACCOUNTS PAYABLE SECTION --}}
    {{-- ============================= --}}
    <div class="card shadow mb-5">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Accounts Payable</h5>
            <div>
                <a href="{{ route('export.payables.pdf') }}" class="btn btn-danger btn-sm me-2">
                    <i class="fe fe-file-text me-1"></i> Export PDF
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPayableModal">
                    <i class="fe fe-plus me-1"></i> Add Payable
                </button>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payment ID</th>
                        <th>Vendor</th>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Mode of Payment</th>
                        <th>Due Date</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th width="90">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payables as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->payment_id }}</td>
                            <td>{{ $item->vendor }}</td>
                            <td>{{ $item->invoice_number ?? '-' }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td>{{ $item->mode_of_payment ?? '-' }}</td>
                            <td>{{ $item->due_date ?? '-' }}</td>
                            <td>{{ $item->payment_date ?? '-' }}</td>
                            <td>
                                <span class="badge 
                                    @if($item->status === 'Paid') bg-success 
                                    @elseif($item->status === 'Unpaid') bg-warning 
                                    @elseif($item->status === 'Overdue') bg-danger 
                                    @else bg-secondary @endif">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>{{ $item->remarks ?? '-' }}</td>
                            <td class="text-center">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#editPayableModal{{ $item->id }}">
                                    <i class="fe fe-edit fe-18 text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center text-muted py-3">No payables found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



    {{-- ============================= --}}
    {{-- ACCOUNTS RECEIVABLE SECTION (PAID COLLECTIONS) --}}
    {{-- ============================= --}}
    <div class="card shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Accounts Receivable — Paid Collections</h5>
            <a href="{{ route('export.receivables.pdf') }}" class="btn btn-danger btn-sm">
                <i class="fe fe-file-text me-1"></i> Export PDF
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Collection ID</th>
                        <th>Customer</th>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Filter all paid collections directly in the view
                        $paidCollections = $collections->where('status', 'Paid');
                    @endphp

                    @forelse($paidCollections as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->collection_id ?? $item->id }}</td>
                            <td>{{ $item->customer_name ?? 'N/A' }}</td>
                            <td>{{ $item->invoice_number ?? '-' }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td>{{ $item->payment_date ?? '-' }}</td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td>{{ $item->remarks ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
                                <i class="fe fe-info me-1"></i> No paid collections found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>



<!-- ============================= -->
<!-- ADD PAYABLE MODAL -->
<!-- ============================= -->
<div class="modal fade" id="addPayableModal" tabindex="-1" aria-labelledby="addPayableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('payables.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addPayableModalLabel">Add Payable</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Vendor</label>
                    <input type="text" name="vendor" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Mode of Payment</label>
                    <select name="mode_of_payment" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Check">Check</option>
                        <option value="Online Payment">Online Payment</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================= --}}
{{-- EDIT PAYABLE MODALS --}}
{{-- ============================= --}}
@foreach($payables as $item)
<div class="modal fade" id="editPayableModal{{ $item->id }}" tabindex="-1" aria-labelledby="editPayableModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('payables.update', $item->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="editPayableModalLabel{{ $item->id }}">Edit Payable</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Vendor</label>
                    <input type="text" name="vendor" class="form-control" value="{{ $item->vendor }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ $item->amount }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Mode of Payment</label>
                    <select name="mode_of_payment" class="form-control">
                        <option value="">-- Select --</option>
                        <option value="Cash" {{ $item->mode_of_payment == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Bank Transfer" {{ $item->mode_of_payment == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="Check" {{ $item->mode_of_payment == 'Check' ? 'selected' : '' }}>Check</option>
                        <option value="Online Payment" {{ $item->mode_of_payment == 'Online Payment' ? 'selected' : '' }}>Online Payment</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ $item->payment_date }}">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $item->due_date }}">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Unpaid" {{ $item->status == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="Paid" {{ $item->status == 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Overdue" {{ $item->status == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ $item->remarks }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection