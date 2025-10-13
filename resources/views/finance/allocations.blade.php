@extends('layouts.app')

@section('title', 'Budget Allocation')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-primary mb-0">Budget Allocation</h2>
            <p class="text-muted mb-0">Plan, allocate, and track your budgets.</p>
        </div>
        <button class="btn btn-indigo text-white btn-primary" data-bs-toggle="modal" data-bs-target="#addAllocationModal">
            <i class="bi bi-plus-lg me-1"></i> New Allocation
        </button>
    </div>

    {{-- flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Budget Plans --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Budget Plans</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request ID</th>
                            <th>Department</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $r)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $r->request_id }}</td>
                                <td>{{ $r->department }}</td>
                                <td>{{ $r->purpose }}</td>
                                <td>₱{{ number_format($r->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $r->status === 'Approved' ? 'success' : ($r->status === 'Pending' ? 'warning' : 'danger') }}">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No budget requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Allocations --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Allocation List</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Department</th>
                            <th>Project</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocations as $a)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $a->department }}</td>
                                <td>{{ $a->project }}</td>
                                <td>₱{{ number_format($a->allocated, 2) }}</td>
                                <td>₱{{ number_format($a->used, 2) }}</td>
                                <td>₱{{ number_format($a->allocated - $a->used, 2) }}</td>
                                <td>{{ optional($a->created_at)->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">
                                        <!-- Calculate Used Icon -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#calculateUsedModal{{ $a->id }}" 
                                        title="Calculate Used" class="text-white text-decoration-none">
                                            <i class="fe fe-bar-chart-2 fe-18"></i>
                                        </a>

                                        <!-- Edit Icon -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#editAllocationModal{{ $a->id }}" 
                                        title="Edit" class="text-white text-decoration-none">
                                            <i class="fe fe-edit fe-18"></i>
                                        </a>

                                        <!-- Delete Icon -->
                                        <form action="{{ route('finance.allocations.destroy', $a->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this allocation?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-transparent border-0 p-0 text-white" title="Delete">
                                                <i class="fe fe-trash fe-18"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Calculate Used Modal --}}
                            <div class="modal fade" id="calculateUsedModal{{ $a->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('finance.allocations.updateUsed', $a->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Used Amount</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label>Department</label>
                                                    <input type="text" class="form-control" value="{{ $a->department }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Project</label>
                                                    <input type="text" class="form-control" value="{{ $a->project }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Allocated</label>
                                                    <input type="number" step="0.01" class="form-control" value="{{ $a->allocated }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Used</label>
                                                    <input name="used" type="number" step="0.01" class="form-control" value="{{ $a->used }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label>Remaining</label>
                                                    <input id="remaining-{{ $a->id }}" type="text" class="form-control" value="{{ number_format($a->allocated - $a->used, 2) }}" disabled>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Edit Allocation Modal --}}
                            <div class="modal fade" id="editAllocationModal{{ $a->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content p-3">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Allocation</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('finance.allocations.update', $a->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label>Department</label>
                                                    <input name="department" type="text" class="form-control" value="{{ $a->department }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Project</label>
                                                    <input name="project" type="text" class="form-control" value="{{ $a->project }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Allocated</label>
                                                    <input name="allocated" type="number" step="0.01" class="form-control" value="{{ $a->allocated }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Used</label>
                                                    <input name="used" type="number" step="0.01" class="form-control" value="{{ $a->used }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No allocations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Allocation Modal --}}
<div class="modal fade" id="addAllocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title">New Allocation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('finance.allocations.store') }}" method="POST" id="allocationForm">
                @csrf
                <div class="modal-body">

                    {{-- Budget Request Dropdown --}}
                    <div class="mb-3">
                        <label>Link to Budget Request (optional)</label>
                        <select name="budget_request_id" id="budget_request_id" class="form-control">
                            <option value="">-- None --</option>
                            @foreach($requests as $r)
                                <option 
                                    value="{{ $r->id }}" 
                                    data-department="{{ $r->department ?? 'N/A' }}">
                                    {{ $r->department ?? 'N/A' }} — {{ $r->request_id }} — ₱{{ number_format($r->amount,2) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Selecting a request will auto-fill its department if available.
                        </small>
                    </div>

                    {{-- Department Field (auto-fill or dropdown) --}}
                    <div class="mb-3" id="deptContainer">
                        <label>Department</label>
                        <input name="department" id="departmentField" type="text" class="form-control" required readonly>
                    </div>

                    {{-- Project --}}
                    <div class="mb-3">
                        <label>Project</label>
                        <input name="project" type="text" class="form-control" required>
                    </div>

                    {{-- Allocated Amount --}}
                    <div class="mb-3">
                        <label>Allocated Amount</label>
                        <input name="allocated" type="number" step="0.01" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reqSelect = document.getElementById('budget_request_id');
    const deptContainer = document.getElementById('deptContainer');
    const form = document.getElementById('allocationForm');

    reqSelect.addEventListener('change', function() {
        const selectedOption = reqSelect.options[reqSelect.selectedIndex];
        const department = selectedOption.getAttribute('data-department');

        // Clear the current container
        deptContainer.innerHTML = '';

        if (!department || department.toLowerCase() === 'n/a') {
            // Show dropdown for department selection
            deptContainer.innerHTML = `
                <label>Department</label>
                <select name="department" id="departmentField" class="form-control" required>
                    <option value="" disabled selected>Select Department</option>
                    <option value="Finance">Finance</option>
                    <option value="Human Resources">Human Resources</option>
                    <option value="Procurement">Procurement</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Operations">Operations</option>
                    <option value="IT and Systems">IT and Systems</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Administration">Administration</option>
                </select>
            `;
        } else {
            // Show readonly text input
            deptContainer.innerHTML = `
                <label>Department</label>
                <input name="department" id="departmentField" type="text" class="form-control" value="${department}" required readonly>
            `;
        }
    });
});
</script>

@push('scripts')
<script>
    // dynamic remaining calculation in calculate modal
    document.addEventListener('show.bs.modal', function (e) {
        const modal = e.target;
        if (modal.matches('[id^="calculateUsedModal"]')) {
            const usedInput = modal.querySelector('input[name="used"]');
            const allocatedInput = modal.querySelector('input[disabled][value]');
            const remaining = modal.querySelector('input[id^="remaining-"]') || null;
            if (!usedInput || !remaining) return;
            usedInput.addEventListener('input', function () {
                const allocated = parseFloat(modal.querySelector('input[disabled][value]').value) || 0;
                const used = parseFloat(usedInput.value) || 0;
                remaining.value = (allocated - used).toFixed(2);
            });
        }
    });
</script>
@endpush