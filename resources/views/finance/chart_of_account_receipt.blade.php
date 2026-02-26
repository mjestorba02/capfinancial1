@extends('layouts.app')

@section('title', 'Account Receipt - ' . $receiptNumber)

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Chart of Accounts – Receipt</h5>
                        <small class="text-muted">Account record summary.</small>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
                </div>
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between">
                        <div>
                            <h6 class="fw-semibold mb-1">Account Name</h6>
                            <p class="mb-0">{{ $account->account_name }}</p>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-semibold mb-1">Receipt No.</h6>
                            <p class="mb-0">{{ $receiptNumber }}</p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%;">Account Code</th>
                                    <td>{{ $account->account_code }}</td>
                                </tr>
                                <tr>
                                    <th>Account Type</th>
                                    <td>{{ $account->account_type }}</td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $account->category }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $account->description ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Balance</th>
                                    <td>₱{{ number_format($account->balance ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted small mb-0">
                        This is a system-generated receipt for this chart of accounts record.
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('chart.index') }}" class="btn btn-secondary btn-sm">Back to Chart of Accounts</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
