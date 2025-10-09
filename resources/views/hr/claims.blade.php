@extends('layouts.app')

@section('title', 'Claims & Reimbursement')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Claims & Reimbursement</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Add Claim Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->id }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Type of Claim</label>
                            <input type="text" name="type_of_claim" class="form-control" placeholder="e.g. Medical, Travel" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Attach Document</label>
                            <input type="file" name="attached_document" class="form-control-file">
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-2">
                            <label>Claim Date</label>
                            <input type="date" name="claim_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Claim Amount</label>
                            <input type="number" step="0.01" name="claim_amount" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Reimbursement Amount</label>
                            <input type="number" step="0.01" name="reimbursement_amount" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Reimbursement Date</label>
                            <input type="date" name="reimbursement_date" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Denied">Denied</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Claim</button>
                </form>
            </div>
        </div>

        {{-- Claims Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Claims List</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Claim ID</th>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Type of Claim</th>
                            <th>Attached Document</th>
                            <th>Claim Date</th>
                            <th>Claim Amount</th>
                            <th>Reimbursement Amount</th>
                            <th>Reimbursement Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($claims as $claim)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $claim->claim_id }}</td>
                            <td>{{ $claim->user->name }}</td>
                            <td>{{ $claim->user->position }}</td>
                            <td>{{ $claim->type_of_claim }}</td>
                            <td>
                                @if($claim->attached_document)
                                    <a href="{{ asset('storage/' . $claim->attached_document) }}" target="_blank">View</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $claim->claim_date }}</td>
                            <td>₱{{ number_format($claim->claim_amount, 2) }}</td>
                            <td>₱{{ number_format($claim->reimbursement_amount, 2) }}</td>
                            <td>{{ $claim->reimbursement_date ?? '-' }}</td>
                            <td>
                                @php
                                    $badgeClass = match($claim->status) {
                                        'Pending' => 'warning',
                                        'Denied'  => 'danger',
                                        default   => 'success',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $claim->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection