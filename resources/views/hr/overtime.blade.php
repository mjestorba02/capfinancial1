@extends('layouts.app')

@section('title', 'Overtime Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Request Overtime</h4>

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Overtime Request Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('overtime.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->id }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label>Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label>End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Reason for overtime">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>

        {{-- Overtime Requests Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Overtime Requests List</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="fms-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $req->employee->name }}</td>
                                    <td>{{ $req->date }}</td>
                                    <td>{{ $req->start_time }}</td>
                                    <td>{{ $req->end_time }}</td>
                                    <td>{{ $req->reason ?? '—' }}</td>
                                    <td>
                                        <span class="badge
                                            @if($req->status === 'Pending') bg-warning
                                            @elseif($req->status === 'Approved') bg-success
                                            @else bg-danger @endif">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                    <td class="fms-actions">
                                        <div class="fms-action-group">
                                            @if($req->status === 'Pending')
                                                <button class="btn btn-success btn-sm"
                                                    onclick="openModal({{ $req->id }}, 'Approved')">
                                                    Approve
                                                </button>
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="openModal({{ $req->id }}, 'Denied')">
                                                    Deny
                                                </button>
                                            @elseif(in_array($req->status, ['Approved', 'Denied']))
                                                -
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No overtime requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmLabel">Delete Overtime Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <strong>delete</strong> this overtime request? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize the modal once DOM is ready
    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);

    let selectedId = null;
    let selectedStatus = null;

    // Called by Approve/Deny buttons
    window.openModal = function (id, status) {
        selectedId = id;
        selectedStatus = status;
        document.getElementById('confirmMessage').textContent = 
            `Are you sure you want to ${status.toLowerCase()} this request?`;
        confirmModal.show();
    };

    // Confirm button
    document.getElementById('confirmAction').addEventListener('click', function () {
        if (!selectedId || !selectedStatus) return;

        fetch(`/hr/overtime/update-status/${selectedId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: selectedStatus }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                confirmModal.hide();
                location.reload();
            }
        });
    });

    //Delete
    // get modal element and initialize bootstrap modal
    const deleteModalEl = document.getElementById('deleteConfirmModal');
    if (!deleteModalEl) return; // safety

    const deleteModal = new bootstrap.Modal(deleteModalEl);
    let deleteId = null;

    // Expose globally so inline onclick can access it
    window.openDeleteModal = function (id) {
        deleteId = id;
        // optionally set some message inside modal if you want
        deleteModal.show();
    };

    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.addEventListener('click', function () {
        if (!deleteId) return;

        // build URL for delete; adjust if your route is named differently
        const url = `/hr/overtime/${deleteId}`;

        // get csrf token (works if you included meta tag in layout)
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '{{ csrf_token() }}';

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(async res => {
            const text = await res.text();
            // server may return HTML on error -> protect parsing
            try {
                const data = text ? JSON.parse(text) : {};
                if (res.ok && data.success) {
                    deleteModal.hide();
                    location.reload();
                } else {
                    // show message returned by server or a fallback
                    const msg = data.message || 'Delete failed.';
                    alert(msg);
                }
            } catch (err) {
                console.error('Unexpected non-JSON response:', text);
                alert('Server error occurred. Check console for details.');
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('Request failed. Check console for details.');
        });
    });
});
</script>
@endsection