@extends('layouts.app')

@section('title', 'Employee Timesheet Report')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Employee Timesheet Report</h4>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('timesheet.report') }}" 
            class="card p-3 shadow-sm mb-4 bg-body-tertiary border-0">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">From</label>
                    <input type="date" name="from" value="{{ $from }}" 
                        class="form-control bg-body text-body" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">To</label>
                    <input type="date" name="to" value="{{ $to }}" 
                        class="form-control bg-body text-body" required>
                </div>

                <div class="col-md-3">
                    <label>Position</label>
                    <select name="position" class="form-control">
                        <option value="">-- All Positions --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ $position == $pos ? 'selected' : '' }}>
                                {{ $pos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>

        {{-- Timesheet Table --}}
        @if(!empty($reportData))
        <div class="card shadow border-0 bg-body-tertiary">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold text-primary">Timesheet Report Results</h5>

                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Total Hours</th>
                            <th>Overtime</th>
                            <th>Undertime</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $index => $r)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $r['employee'] }}</td>
                                <td>{{ $from }}</td>
                                <td>{{ $to }}</td>
                                <td>{{ $r['position'] }}</td>
                                <td>{{ $r['department'] }}</td>
                                <td>{{ $r['total_hours'] }}</td>
                                <td>{{ $r['overtime'] }}</td>
                                <td>{{ $r['undertime'] }}</td>
                                <td>
                                    {{-- View button --}}
                                    <button type="button"
                                        class="btn btn-sm btn-info viewTimesheet"
                                        data-id="{{ $r['employee_id'] ?? '' }}"
                                        data-employee="{{ $r['employee'] ?? '' }}"
                                        data-position="{{ $r['position'] ?? '' }}"
                                        data-department="{{ $r['department'] ?? '' }}"
                                        data-from="{{ $from }}"
                                        data-to="{{ $to }}">
                                        View
                                    </button>

                                    {{-- Download button --}}
                                    <a href="{{ route('timesheet.download', $r['employee']) }}?from={{ $from }}&to={{ $to }}"
                                        class="btn btn-sm btn-success">
                                        <i class="fas fa-file-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Totals:</td>
                            <td>{{ number_format(collect($reportData)->sum('total_hours'), 2) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('overtime'), 2) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('undertime'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @elseif(request()->has('from'))
            <div class="alert alert-warning border-0 text-center">No records found for the selected filters.</div>
        @endif
    </div>
</div>

{{-- Timesheet Details Modal --}}
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-labelledby="timesheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timesheetModalLabel">Timesheet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Employee:</strong> <span id="ts-employee"></span></p>
                <p><strong>Department:</strong> <span id="ts-department"></span></p>
                <p><strong>Position:</strong> <span id="ts-position"></span></p>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Total Hours</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="timesheetDetails">
                        <tr>
                            <td colspan="6" class="text-center text-muted">No records loaded</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Totals:</td>
                            <td id="ts-total-hours">0</td>
                            <td id="ts-total-overtime">0</td>
                            <td>-</td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Signature Section --}}
                <div class="mt-5">
                    <h6>Signature</h6>
                    <div class="border border-dark rounded" style="height:100px;"></div>
                    <p class="text-muted small mt-2">Employee Signature</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Logic --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".viewTimesheet").forEach(btn => {
        btn.addEventListener("click", async function () {
            let id   = this.dataset.id;
            let from = this.dataset.from;
            let to   = this.dataset.to;

            if (!id) {
                alert("Employee ID not found for this record.");
                return;
            }

            // Fill header info
            document.getElementById("ts-employee").textContent = this.dataset.employee || "-";
            document.getElementById("ts-position").textContent = this.dataset.position || "-";
            document.getElementById("ts-department").textContent = this.dataset.department || "-";

            try {
                // Fetch records with date range
                let res = await fetch(`/hr/timesheet/employee/${id}/details?from=${from}&to=${to}`);
                if (!res.ok) throw new Error(`Server error: ${res.status}`);

                let data = await res.json();
                let rows = "";

                if (data.records && data.records.length > 0) {
                    data.records.forEach(r => {
                        rows += `
                        <tr>
                            <td>${r.date ?? "-"}</td>
                            <td>${r.start_time ?? "-"}</td>
                            <td>${r.end_time ?? "-"}</td>
                            <td>${r.hours_worked ?? "0"}</td>
                            <td>${r.overtime ?? "0"}</td>
                            <td>${r.status ?? "-"}</td>
                        </tr>`;
                    });

                    document.getElementById("ts-total-hours").textContent = data.totals.hours_worked ?? "0";
                    document.getElementById("ts-total-overtime").textContent = data.totals.overtime ?? "0";
                } else {
                    rows = `<tr>
                        <td colspan="6" class="text-center text-muted">No records found</td>
                    </tr>`;
                    document.getElementById("ts-total-hours").textContent = "0";
                    document.getElementById("ts-total-overtime").textContent = "0";
                }

                document.getElementById("timesheetDetails").innerHTML = rows;

            } catch (err) {
                console.error("Fetch error:", err);
                document.getElementById("timesheetDetails").innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Failed to load timesheet.
                        </td>
                    </tr>`;
            }

            // Show modal
            const modalEl = document.getElementById("timesheetModal");
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });
});
</script>
@endsection