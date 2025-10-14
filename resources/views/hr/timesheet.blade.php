@extends('layouts.app')

@section('title', 'Employee Timesheet')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Employee Timesheet</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Add/Edit Timesheet Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form id="timesheetForm" method="POST" action="{{ route('timesheet.store') }}">
                    @csrf
                    <input type="hidden" name="timesheet_id" id="timesheet_id">

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="employee_id">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="date_from">From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="date_to">To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" required>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="notes">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="1" placeholder="Enter remarks..."></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" id="formSubmitBtn" class="btn btn-success">
                            <i class="fas fa-save"></i> Generate Timesheet
                        </button>
                        <button type="button" id="cancelEditBtn" class="btn btn-secondary d-none" onclick="resetForm()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Timesheet Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Timesheet Records</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Position</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timesheets as $sheet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $sheet->employee->name }}</td>
                                <td>{{ $sheet->from_date }}</td>
                                <td>{{ $sheet->to_date }}</td>
                                <td>{{ $sheet->position }}</td>
                                <td>{{ $sheet->notes }}</td>
                                <td>
                                    {{-- Edit button --}}
                                    <button type="button" 
                                        class="btn btn-sm btn-warning"
                                        onclick="editTimesheet({{ $sheet }})">
                                        Edit
                                    </button>

                                    {{-- View button --}}
                                    <button type="button"
                                        class="btn btn-sm btn-info viewTimesheet"
                                        data-id="{{ $sheet->employee_id }}"
                                        data-employee="{{ $sheet->employee->name }}"
                                        data-position="{{ $sheet->employee->position }}"
                                        data-department="{{ $sheet->employee->department }}"
                                        data-from="{{ $sheet->from_date }}"
                                        data-to="{{ $sheet->to_date }}">
                                        View
                                    </button>

                                    {{-- Delete form --}}
                                    <!-- <form action="{{ route('timesheet.destroy', $sheet->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</button>
                                    </form> -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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

<script>
    function editTimesheet(sheet) {
        document.getElementById('timesheet_id').value = sheet.id;
        document.getElementById('employee_id').value = sheet.employee_id;
        document.getElementById('date_from').value = sheet.date;
        document.getElementById('date_to').value = sheet.date;
        document.getElementById('notes').value = sheet.notes;

        let form = document.getElementById('timesheetForm');
        form.action = `/hr/timesheet/${sheet.id}`;
        if (!form.querySelector('input[name="_method"]')) {
            let method = document.createElement("input");
            method.type = "hidden";
            method.name = "_method";
            method.value = "PUT";
            form.appendChild(method);
        }

        document.getElementById('formSubmitBtn').innerText = "Update Timesheet";
        document.getElementById('cancelEditBtn').classList.remove("d-none");
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".viewTimesheet").forEach(btn => {
            btn.addEventListener("click", async function () {
                let id   = this.dataset.id;
                let from = this.dataset.from;
                let to   = this.dataset.to;

                // Fill header info
                document.getElementById("ts-employee").textContent = this.dataset.employee || "-";
                document.getElementById("ts-position").textContent = this.dataset.position || "-";
                document.getElementById("ts-department").textContent = this.dataset.department || "-";

                try {
                    // Fetch records with range
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
                            <td colspan="5" class="text-center text-danger">
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