@extends('layouts.employee')

@section('title', 'My Budget Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">My Budget Requests</h4>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.budget.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Reason (Purpose)</label>
                            <input type="text" name="purpose" class="form-control" required placeholder="e.g. Office supplies, project materials">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount (₱)</label>
                            <input type="number" name="amount" class="form-control" required min="1" max="5000000" step="0.01" placeholder="Enter amount (₱)">
                            <small class="text-muted">Max: ₱5,000,000</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Details</label>
                            <textarea name="details" class="form-control" rows="2" placeholder="Additional details (optional)"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional remarks">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attach PDF or Image</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,image/jpeg,image/jpg,image/png,image/gif">
                            <small class="text-muted">PDF, JPG, PNG, GIF. Max 5MB.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Submitted Requests</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Employee Name</th>
                                <th>Reason</th>
                                <th>Amount</th>
                                <th>Details</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                                <th>Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td><strong>{{ $req->request_id }}</strong></td>
                                <td>{{ $req->name ?? ($req->employee->name ?? '—') }}</td>
                                <td>{{ $req->purpose }}</td>
                                <td>₱{{ number_format($req->amount, 2) }}</td>
                                <td>{{ Str::limit($req->details, 40) ?: '—' }}</td>
                                <td>{{ $req->created_at->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $status = $req->status;
                                        $badge = match($status) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            'Pending Admin' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $status }}</span>
                                </td>
                                <td>
                                    @if($req->attachment_path)
                                        <a href="{{ asset('storage/' . $req->attachment_path) }}" target="_blank" class="text-primary small">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@endsection
