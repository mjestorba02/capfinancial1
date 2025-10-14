@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Chart of Accounts</h2>
            <p class="text-muted mb-0">Manage financial account records and categories.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Account</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Accounts Table --}}
    <div class="card shadow">
        <div class="card-body">
            <h5 class="mb-3">List of Accounts</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th>Account Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <!-- <th>Balance</th> -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $account->account_code }}</td>
                            <td>{{ $account->account_name }}</td>
                            <td>{{ $account->account_type }}</td>
                            <td>{{ $account->category }}</td>
                            <td>{{ $account->description ?? '-' }}</td>
                            <!-- <td class="text-end">₱{{ number_format($account->balance, 2) }}</td> -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <!-- Edit -->
                                    <a href="#" class="text-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $account->id }}">
                                        <i class="fe fe-edit fe-18"></i>
                                    </a>
                                    <!-- Delete -->
                                    <!-- <form action="{{ route('chart.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 m-0" title="Delete">
                                            <i class="fe fe-trash fe-18"></i>
                                        </button>
                                    </form> -->
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i> No accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                <h6 class="fw-semibold">Totals</h6>
                <p class="mb-1">Total Assets: <strong>₱{{ number_format($totalAssets, 2) }}</strong></p>
                <p class="mb-1">Total Liabilities: <strong>₱{{ number_format($totalLiabilities, 2) }}</strong></p>
                <p>Total Equity: <strong>₱{{ number_format($totalEquity, 2) }}</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('chart.store') }}" class="modal-content">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="addModalLabel">Add New Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Code</label>
                    <input type="text" name="account_code" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Name</label>
                    <input type="text" name="account_name" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Type</label>
                    <select name="account_type" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Asset">Asset</option>
                        <option value="Liability">Liability</option>
                        <option value="Equity">Equity</option>
                        <option value="Revenue">Revenue</option>
                        <option value="Expense">Expense</option>
                        <option value="Income">Income</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="Current Asset">Current Asset</option>
                        <option value="Fixed Asset">Fixed Asset</option>
                        <option value="Current Liability">Current Liability</option>
                        <option value="Long-Term Liability">Long-Term Liability</option>
                        <option value="Owner's Equity">Owner's Equity</option>
                        <option value="Operating Revenue">Operating Revenue</option>
                        <option value="Operating Expense">Operating Expense</option>
                        <option value="COGS">COGS</option>
                        <option value="Non-operating Expense">Non-operating Expense</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>

                <!-- <div class="form-group mb-3">
                    <label class="fw-semibold">Initial Balance</label>
                    <input type="number" step="0.01" name="balance" class="form-control" value="0.00">
                </div> -->
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Save</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@foreach($accounts as $account)
<div class="modal fade" id="editModal{{ $account->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $account->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('chart.update', $account->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="editModalLabel{{ $account->id }}">Edit Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Code</label>
                    <input type="text" name="account_code" class="form-control" value="{{ $account->account_code }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Name</label>
                    <input type="text" name="account_name" class="form-control" value="{{ $account->account_name }}" required>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Account Type</label>
                    <select name="account_type" class="form-control" required>
                        <option value="Asset" {{ $account->account_type === 'Asset' ? 'selected' : '' }}>Asset</option>
                        <option value="Liability" {{ $account->account_type === 'Liability' ? 'selected' : '' }}>Liability</option>
                        <option value="Equity" {{ $account->account_type === 'Equity' ? 'selected' : '' }}>Equity</option>
                        <option value="Revenue" {{ $account->account_type === 'Revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="Expense" {{ $account->account_type === 'Expense' ? 'selected' : '' }}>Expense</option>
                        <option value="Income" {{ $account->account_type === 'Income' ? 'selected' : '' }}>Income</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Current Asset" {{ $account->category === 'Current Asset' ? 'selected' : '' }}>Current Asset</option>
                        <option value="Fixed Asset" {{ $account->category === 'Fixed Asset' ? 'selected' : '' }}>Fixed Asset</option>
                        <option value="Current Liability" {{ $account->category === 'Current Liability' ? 'selected' : '' }}>Current Liability</option>
                        <option value="Long-Term Liability" {{ $account->category === 'Long-Term Liability' ? 'selected' : '' }}>Long-Term Liability</option>
                        <option value="Owner's Equity" {{ $account->category === "Owner's Equity" ? 'selected' : '' }}>Owner's Equity</option>
                        <option value="Operating Revenue" {{ $account->category === 'Operating Revenue' ? 'selected' : '' }}>Operating Revenue</option>
                        <option value="Operating Expense" {{ $account->category === 'Operating Expense' ? 'selected' : '' }}>Operating Expense</option>
                        <option value="COGS" {{ $account->category === 'COGS' ? 'selected' : '' }}>COGS</option>
                        <option value="Non-operating Expense" {{ $account->category === 'Non-operating Expense' ? 'selected' : '' }}>Non-operating Expense</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ $account->description }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="fw-semibold">Balance</label>
                    <input type="number" step="0.01" name="balance" class="form-control" value="{{ $account->balance }}">
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