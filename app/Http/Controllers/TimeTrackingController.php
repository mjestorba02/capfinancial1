<?php

namespace App\Http\Controllers;

use App\Models\TimeTracking;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Shift;

class TimeTrackingController extends Controller
{
    public function index()
    {
        $records = TimeTracking::with('employee')->latest()->get();
        $employees = User::all();

        return view('hr.time-tracking', compact('records','employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date'        => 'required|date',
            'time_in'     => 'required',
            'time_out'    => 'required',
        ]);

        $date    = $request->date;
        $timeIn  = Carbon::parse("{$date} {$request->time_in}");
        $timeOut = Carbon::parse("{$date} {$request->time_out}");

        // If timeout is earlier or equal to timein, assume next day (overnight shift)
        if ($timeOut->lte($timeIn)) {
            $timeOut->addDay();
        }

        $dayName = Carbon::parse($date)->format('l');

        $status    = 'Present';
        $overtime  = 0;
        $undertime = 0;

        // ✅ Always positive total hours
        $totalHours = abs(round($timeOut->diffInSeconds($timeIn) / 3600, 2));

        // Find employee's shift for that day
        $shift = Shift::where('employee_id', $request->employee_id)
            ->whereJsonContains('days', $dayName)
            ->first();

        if ($shift) {
            $shiftStart = Carbon::parse("{$date} {$shift->start_time}");
            $shiftEnd   = Carbon::parse("{$date} {$shift->end_time}");

            // Handle shifts that cross midnight
            if ($shiftEnd->lte($shiftStart)) {
                $shiftEnd->addDay();
            }

            // Late check
            if ($timeIn->gt($shiftStart)) {
                $status = 'Late';
            }

            // ✅ Always positive overtime
            if ($timeOut->gt($shiftEnd)) {
                $overtime = abs(round($timeOut->diffInSeconds($shiftEnd) / 3600, 2));
            }

            // ✅ Always positive undertime
            if ($timeOut->lt($shiftEnd)) {
                $undertime = abs(round($shiftEnd->diffInSeconds($timeOut) / 3600, 2));
                $status = ($status === 'Late') ? 'Late & Undertime' : 'Undertime';
            }
        } else {
            $status = 'No Schedule';
        }

        TimeTracking::create([
            'employee_id' => $request->employee_id,
            'date'        => $date,
            'time_in'     => $request->time_in,
            'time_out'    => $request->time_out,
            'total_hours' => $totalHours,
            'overtime'    => $overtime,
            'undertime'   => $undertime,
            'status'      => $status,
        ]);

        return redirect()->route('timetracking.index')
                        ->with('success', 'Time record saved successfully.');
    }
}