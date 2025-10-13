@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Journal Entries</h2>
            <p class="text-muted mb-0">Manage and track accounting journal entries.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Journal Entries Table -->
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">Journal Entries List</h5>

            <!-- Filter Section -->
            <form method="GET" action="{{ route('journal_entries.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label">Filter by Account</label>
                    <input type="text" name="account" class="form-control form-control-sm" placeholder="Account Name" value="{{ request('account') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                    <a href="{{ route('journal_entries.index') }}" class="btn btn-secondary btn-sm flex-grow-1">Reset</a>
                </div>
            </form>

            <!-- Table -->
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Account</th>
                        <th>Credit</th>
                        <th>Debit</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $journal->account }}</td>
                            <td>₱{{ number_format($journal->credit, 2) }}</td>
                            <td>₱{{ number_format($journal->debit, 2) }}</td>
                            <td>{{ $journal->description ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($journal->entry_date)->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-3">
                                    <!-- Edit -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $journal->id }}" class="text-primary">
                                        <i class="fe fe-edit fe-18"></i>
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('journal_entries.destroy', $journal->id) }}" method="POST" onsubmit="return confirm('Delete this journal entry?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-transparent border-0 text-danger p-0">
                                            <i class="fe fe-trash fe-18"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i> No journal entries found.
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
        <form method="POST" action="{{ route('journal_entries.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addModalLabel">Add Journal Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="fw-semibold">Account</label>
                    <input type="text" name="account" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm" required>
                        <option value="Debit">Debit</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Description</label>
                    <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Date</label>
                    <input type="date" name="entry_date" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals -->
@foreach($journals as $journal)
<div class="modal fade" id="editModal{{ $journal->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $journal->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('journal_entries.update', $journal->id) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel{{ $journal->id }}">Edit Journal Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="fw-semibold">Account</label>
                    <input type="text" name="account" class="form-control form-control-sm" value="{{ $journal->account }}" required>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Credit</label>
                    <input type="number" step="0.01" name="credit" class="form-control form-control-sm" value="{{ $journal->credit }}">
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Debit</label>
                    <input type="number" step="0.01" name="debit" class="form-control form-control-sm" value="{{ $journal->debit }}">
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Description</label>
                    <textarea name="description" class="form-control form-control-sm" rows="2">{{ $journal->description }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="fw-semibold">Date</label>
                    <input type="date" name="entry_date" class="form-control form-control-sm" value="{{ $journal->entry_date }}">
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