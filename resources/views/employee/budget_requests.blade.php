@extends('layouts.employee')

@section('title', 'My Budget Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">My Budget Requests</h4>
        <p class="page-subtitle text-muted">Submit new requests and track status of existing ones.</p>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @isset($monthlyLimit)
        <div class="alert alert-info mt-3">
            <strong>Monthly budget limit:</strong> ₱{{ number_format($monthlyLimit, 2) }} |
            <strong>Used this month:</strong> ₱{{ number_format($monthlyTotal ?? 0, 2) }} |
            <strong>Remaining:</strong> ₱{{ number_format($remainingBudget ?? 0, 2) }}
        </div>
        @endisset

        @isset($canSubmitBudgetRequest)
            @if(!$canSubmitBudgetRequest)
                <div class="alert alert-warning mt-2">
                    You have reached your monthly budget request limit of ₱{{ number_format($monthlyLimit ?? 50000, 2) }}. You cannot submit additional requests this month.
                </div>
            @endif
        @endisset

        <div class="card shadow-sm section-card mb-4">
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
                            <label class="form-label">Details <span class="text-danger">*</span></label>
                            <textarea name="details" class="form-control" rows="2" required placeholder="Provide details for this request"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attach PDF or Image</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,image/jpeg,image/jpg,image/png,image/gif">
                            <small class="text-muted">PDF, JPG, PNG, GIF. Max 5MB.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                @isset($canSubmitBudgetRequest)
                                    @if(!$canSubmitBudgetRequest) disabled @endif
                                @endisset
                            >
                                Submit Request
                            </button>
                            @isset($canSubmitBudgetRequest)
                                @if(!$canSubmitBudgetRequest)
                                    <small class="text-danger ms-2">Monthly limit reached.</small>
                                @endif
                            @endisset
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm section-card">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Submitted Requests</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th class="cell-employee-name">Employee Name</th>
                                <th>Reason</th>
                                <th>Amount</th>
                                <th>Details</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                                <th>Attachment</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td><strong>{{ $req->request_id }}</strong></td>
                                <td class="cell-employee-name">{{ $req->name ?? ($req->employee->name ?? '—') }}</td>
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
                                <td>
                                    @if($req->remarks)
                                        <a href="#" class="text-primary small" data-bs-toggle="modal" data-bs-target="#remarksModal{{ $req->id }}">View</a>
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
}</div>

@foreach($requests as $req)
    @if($req->remarks)
    <div class="modal fade" id="remarksModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remarks for {{ $req->request_id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ $req->remarks }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
