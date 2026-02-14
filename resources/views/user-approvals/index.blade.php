@extends('layouts.app')

@section('title', 'User Approvals')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">User Approvals</h2>
            <p class="text-muted mb-0">Review and approve or reject pending account registrations.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Success / Email sent modal --}}
    @if(session('success'))
        <div class="modal fade" id="approvalSuccessModal" tabindex="-1" aria-labelledby="approvalSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 bg-success text-white">
                        <h5 class="modal-title d-flex align-items-center" id="approvalSuccessModalLabel">
                            <i class="fe fe-check-circle me-2"></i> Account approved
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p class="mb-0">{{ session('success') }}</p>
                        <p class="mb-0 mt-2 text-success fw-semibold">
                            <i class="fe fe-mail me-1"></i> Email sent to the user.
                        </p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('approvalSuccessModal'));
                modal.show();
            });
        </script>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Pending Admins</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Registered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->position }}</td>
                            <td>{{ $user->department }}</td>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">
                                    <form method="POST" action="{{ route('user-approvals.approve', $user) }}"
                                        onsubmit="return confirm('Approve this account? The user will be able to log in.')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fe fe-check-circle me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('user-approvals.reject', $user) }}"
                                        onsubmit="return confirm('Reject this account? The user will not be able to log in.')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fe fe-x-circle me-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i> No pending admin accounts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Pending Employees</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Registered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingEmployees as $employee)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->department }}</td>
                            <td>{{ $employee->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">
                                    <form method="POST" action="{{ route('user-approvals.employee.approve', $employee) }}"
                                        onsubmit="return confirm('Approve this employee? They will be able to log in to the employee portal.')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="fe fe-check-circle me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('user-approvals.employee.reject', $employee) }}"
                                        onsubmit="return confirm('Reject this employee? They will not be able to log in.')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fe fe-x-circle me-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="fe fe-info me-2"></i> No pending employee accounts.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
