<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use PDF;

class TimesheetController extends Controller
{
    public function index()
    {
        $timesheets = Timesheet::with('employee')->latest()->get();
        $employees = User::all();

        return view('hr.timesheet', compact('timesheets', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'notes'       => 'nullable|string',
        ]);

        $data = $this->buildFromAttendance(
            $request->employee_id,
            $request->notes,
            $request->date_from,
            $request->date_to
        );

        if (! $data) {
            return back()->withErrors([
                'date_from' => 'No time & attendance records found in the selected range.'
            ]);
        }

        Timesheet::create($data);

        return redirect()->route('timesheet.index')
                        ->with('success', "1 timesheet record saved from attendance.");
    }

    protected function buildFromAttendance($employeeId, $notes, $dateFrom, $dateTo)
    {
        // Get attendance logs for the range
        $attendance = \App\Models\TimeTracking::where('employee_id', $employeeId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        if ($attendance->isEmpty()) {
            return null;
        }

        $employee = \App\Models\User::find($employeeId);

        // Calculate total hours & overtime across the range
        $totalHours = 0;
        $overtime   = 0;

        foreach ($attendance as $log) {
            if ($log->start_time && $log->end_time) {
                $start  = Carbon::parse($log->start_time);
                $end    = Carbon::parse($log->end_time);
                $hours  = $end->floatDiffInHours($start);

                $totalHours += $hours;

                if ($hours > 8) {
                    $overtime += ($hours - 8);
                }
            }
        }

        return [
            'employee_id' => $employeeId,
            'from_date'   => $dateFrom,
            'to_date'     => $dateTo,
            'hours_worked'=> $totalHours,
            'overtime'    => $overtime,
            'position'    => $employee->position ?? null,
            'notes'       => $notes,
            'day'         => null,
        ];
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'notes'       => 'nullable|string',
        ]);

        $period = CarbonPeriod::create($request->date_from, $request->date_to);

        $updated = 0;
        foreach ($period as $date) {
            $data = $this->buildFromAttendance($request->employee_id, $date->format('Y-m-d'), $request->notes);

            if ($data) {
                $timesheet->update($data);
                $updated++;
            }
        }

        if ($updated === 0) {
            return back()->withErrors(['date_from' => 'No attendance record found for the selected range.']);
        }

        return redirect()->route('timesheet.index')
                         ->with('success', "$updated timesheet record(s) updated from attendance.");
    }

    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();

        return redirect()->route('timesheet.index')
                         ->with('success', 'Timesheet record deleted.');
    }

    public function details(Request $request, $employeeId)
    {
        $from = $request->date_from ?? $request->from;
        $to   = $request->date_to   ?? $request->to;

        $employeeRecords = \App\Models\TimeTracking::with('employee')
            ->where('employee_id', $employeeId)
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->orderBy('date', 'asc')
            ->get();

        if ($employeeRecords->isEmpty()) {
            return response()->json([
                'message' => 'No records found.',
                'records' => []
            ]);
        }

        $employee = $employeeRecords->first()->employee;

        $records = $employeeRecords->map(function ($r) {
            $start = $r->time_in ? Carbon::parse($r->time_in) : null;
            $end   = $r->time_out ? Carbon::parse($r->time_out) : null;

            return [
                'date'         => $r->date,
                'start_time'   => $start ? $start->format('H:i') : '-',
                'end_time'     => $end ? $end->format('H:i') : '-',
                'hours_worked' => $r->total_hours ?? 0,
                'overtime'     => $r->overtime ?? 0,
                'status'       => $r->status ?? '-',
            ];
        });

        // Totals from DB values
        $totals = [
            'hours_worked' => $records->sum('hours_worked'),
            'overtime'     => $records->sum('overtime'),
        ];

        return response()->json([
            'employee_id' => $employee->id,
            'employee'   => $employee->name ?? 'N/A',
            'department' => $employee->department ?? 'N/A',
            'position'   => $employee->position ?? 'N/A',
            'date_from'  => $from,
            'date_to'    => $to,
            'records'    => $records,
            'totals'     => $totals,
        ]);
    }

    public function report(Request $request)
    {
        $positions = \App\Models\User::select('position')->distinct()->pluck('position');

        $from = $request->from;
        $to = $request->to;
        $position = $request->position;

        $reportData = [];

        if ($from && $to) {
            // Get all time tracking records joined with users
            $query = \App\Models\TimeTracking::with('employee')
                ->whereBetween('date', [$from, $to]);

            if ($position) {
                $query->whereHas('employee', function ($q) use ($position) {
                    $q->where('position', $position);
                });
            }

            $records = $query->get();

            // Group by employee
            $grouped = $records->groupBy('employee_id');

            foreach ($grouped as $empId => $logs) {
                $employee = $logs->first()->employee;

                $totalHours = $logs->sum('total_hours');
                $overtime = $logs->sum('overtime');
                $undertime = $logs->sum('undertime');

                $reportData[] = [
                    'employee_id' => $employee->id,
                    'employee' => $employee->name,
                    'position' => $employee->position ?? 'N/A',
                    'department' => $employee->department ?? 'N/A',
                    'total_hours' => round($totalHours, 2),
                    'overtime' => round($overtime, 2),
                    'undertime' => round($undertime, 2),
                ];
            }
        }

        return view('hr.timesheet_report', compact('positions', 'reportData', 'from', 'to', 'position'));
    }

    public function download($employee, Request $request)
    {
        // Filter the data for that specific employee
        $from = $request->query('from');
        $to = $request->query('to');

        $records = \App\Models\TimeTracking::whereHas('employee', function($q) use ($employee) {
                $q->where('name', $employee);
            })
            ->whereBetween('date', [$from, $to])
            ->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'No timesheet data found for this employee.');
        }

        $employeeInfo = $records->first()->employee;

        // Total calculations
        $totalHours = $records->sum('total_hours');
        $totalOvertime = $records->sum('overtime');
        $totalUndertime = $records->sum('undertime');

        $pdf = PDF::loadView('timesheet.pdf', [
            'employee' => $employeeInfo,
            'records' => $records,
            'from' => $from,
            'to' => $to,
            'totalHours' => $totalHours,
            'totalOvertime' => $totalOvertime,
            'totalUndertime' => $totalUndertime,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Timesheet_{$employee}_{$from}_to_{$to}.pdf");
    }
}