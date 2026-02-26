@extends('layouts.app')

@section('title', 'Journal Entry Receipt - ' . $receiptNumber)

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Journal Entry – Receipt</h5>
                        <small class="text-muted">Transaction record summary.</small>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
                </div>
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between">
                        <div>
                            <h6 class="fw-semibold mb-1">Account</h6>
                            <p class="mb-0">{{ $journal->account }}</p>
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
                                    <th style="width: 40%;">Entry Date</th>
                                    <td>{{ \Carbon\Carbon::parse($journal->entry_date)->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Debit</th>
                                    <td>₱{{ number_format($journal->debit, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Credit</th>
                                    <td>₱{{ number_format($journal->credit, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $journal->description ?? '—' }}</td>
                                </tr>
                                @if($journal->source_module ?? null)
                                <tr>
                                    <th>Source</th>
                                    <td>{{ $journal->source_module }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted small mb-0">
                        This is a system-generated receipt for this journal entry.
                    </p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('journal_entries.index') }}" class="btn btn-secondary btn-sm">Back to Journal Entries</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
