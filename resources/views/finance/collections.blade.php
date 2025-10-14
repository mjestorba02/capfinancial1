@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Accounts Receivable - Collections</h2>
            <p class="text-muted mb-0">Track and record customer payments and invoices.</p>
        </div>
        <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Collection</button> -->
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Collections Table --}}
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">Collections Summary</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Invoice #</th>
                        <th>Amount Due</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->customer_name }}</td>
                            <td>{{ $item->invoice_number }}</td>
                            <td>₱{{ number_format($item->amount_due, 2) }}</td>
                            <td>₱{{ number_format($item->amount_paid, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($item->status) {
                                        'Paid' => 'success',
                                        'Overdue' => 'danger',
                                        'Pending' => 'warning',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>{{ $item->payment_date ?? '-' }}</td>
                            <td>{{ $item->remarks ?? '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">
                                    <!-- Approve Icon -->
                                    @if($item->status !== 'Paid')
                                    <form method="POST" action="{{ route('collections.approve', $item->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="bg-transparent border-0 p-0 text-success" title="Approve Payment"
                                            onclick="return confirm('Mark this collection as Paid?')">
                                            <i class="fe fe-check-circle fe-18"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <!-- Edit Icon -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}" 
                                    title="Edit" class="text-white text-decoration-none">
                                        <i class="fe fe-edit fe-18"></i>
                                    </a>

                                    <!-- Delete Icon -->
                                    <!-- <form method="POST" action="{{ route('collections.destroy', $item->id) }}" 
                                        onsubmit="return confirm('Delete this record?')" 
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-transparent border-0 p-0 text-danger" title="Delete">
                                            <i class="fe fe-trash fe-18"></i>
                                        </button>
                                    </form> -->
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i> No collections found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('collections.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addModalLabel">Add Collection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount Due</label>
                    <input type="number" step="0.01" name="amount_due" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount Paid</label>
                    <input type="number" step="0.01" name="amount_paid" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Pending">Pending</option>
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
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@foreach($collections as $item)
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('collections.update', $item->id) }}" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Edit Collection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Customer Name</label>
          <input type="text" name="customer_name" class="form-control" value="{{ $item->customer_name }}" required>
        </div>
        <div class="mb-3">
          <label>Invoice Number</label>
          <input type="text" name="invoice_number" class="form-control" value="{{ $item->invoice_number }}" readonly>
        </div>
        <div class="mb-3">
          <label>Amount Due</label>
          <input type="number" step="0.01" name="amount_due" class="form-control" value="{{ $item->amount_due }}" required>
        </div>
        <div class="mb-3">
          <label>Amount Paid</label>
          <input type="number" step="0.01" name="amount_paid" class="form-control" value="{{ $item->amount_paid }}">
        </div>
        <div class="mb-3">
          <label>Payment Date</label>
          <input type="date" name="payment_date" class="form-control" value="{{ $item->payment_date }}">
        </div>
        <div class="mb-3">
          <label>Status</label>
          <select name="status" class="form-select" required>
            <option value="Pending" {{ $item->status === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Paid" {{ $item->status === 'Paid' ? 'selected' : '' }}>Paid</option>
            <option value="Overdue" {{ $item->status === 'Overdue' ? 'selected' : '' }}>Overdue</option>
          </select>
        </div>
        <div class="mb-3">
          <label>Remarks</label>
          <textarea name="remarks" class="form-control">{{ $item->remarks }}</textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
@endforeach

@endsection