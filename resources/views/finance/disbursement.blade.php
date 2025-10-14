@extends('layouts.app')

@section('title', 'Disbursements')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Finance - Disbursements</h2>
            <p class="text-muted mb-0">Manage and track company disbursement transactions.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Disbursement</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Disbursement Table --}}
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">Disbursement Summary</h5>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voucher No</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disbursements as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->voucher_no }}</td>
                            <td>{{ $item->vendor }}</td>
                            <td>{{ $item->category }}</td>
                            <td>₱{{ number_format($item->amount, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($item->status) {
                                        'Released' => 'success',
                                        'Pending' => 'warning',
                                        'Cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">{{ $item->status }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->disbursement_date)->format('M d, Y') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    {{-- Edit --}}
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}" 
                                       class="text-primary" title="Edit">
                                        <i class="fe fe-edit fe-18"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <!-- <form method="POST" action="{{ route('disbursements.destroy', $item->id) }}"
                                          onsubmit="return confirm('Delete this record?')" class="d-inline">
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
                            <td colspan="8" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i>No disbursements found.
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
        <form method="POST" action="{{ route('disbursements.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addModalLabel">Add Disbursement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Vendor</label>
                    <input type="text" name="vendor" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Category</label>
                    <input type="text" name="category" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Pending">Pending</option>
                        <option value="Released">Released</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Disbursement Date</label>
                    <input type="date" name="disbursement_date" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($disbursements as $item)
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('disbursements.update', $item->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="editModalLabel{{ $item->id }}">Edit Disbursement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Vendor</label>
                    <input type="text" name="vendor" value="{{ $item->vendor }}" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Category</label>
                    <input type="text" name="category" value="{{ $item->category }}" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Amount</label>
                    <input type="number" step="0.01" name="amount" value="{{ $item->amount }}" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Pending" {{ $item->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Released" {{ $item->status === 'Released' ? 'selected' : '' }}>Released</option>
                        <option value="Cancelled" {{ $item->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Disbursement Date</label>
                    <input type="date" name="disbursement_date" value="{{ $item->disbursement_date }}" class="form-control" required>
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