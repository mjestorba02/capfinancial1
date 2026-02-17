@extends('layouts.employee')

@section('title', 'My Budget Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="mb-4">My Budget Requests</h4>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.budget.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" required min="1" max="5000000" step="0.01" placeholder="Enter amount (₱)">
                            <small class="text-muted">Max: ₱5,000,000</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-primary w-100">Submit</button>
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
                                <th>#</th>
                                <th>Purpose</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $req->purpose }}</td>
                                <td>₱{{ number_format($req->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $req->status === 'Approved' ? 'success' : ($req->status === 'Rejected' ? 'danger' : 'secondary') }}">{{ $req->status }}</span>
                                </td>
                                <td>{{ $req->remarks }}</td>
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
