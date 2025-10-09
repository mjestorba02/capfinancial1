<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timesheet Report - {{ $employee->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        h2, h4 { text-align: center; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #555; padding: 8px; text-align: center; font-size: 13px; }
        th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <h2>Employee Timesheet Report</h2>
    <h4>{{ $employee->name }} ({{ $employee->position }})</h4>
    <p style="text-align:center;">From: {{ $from }} — To: {{ $to }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Total Hours</th>
                <th>Overtime</th>
                <th>Undertime</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->date }}</td>
                <td>{{ $r->time_in }}</td>
                <td>{{ $r->time_out }}</td>
                <td>{{ number_format($r->total_hours, 2) }}</td>
                <td>{{ number_format($r->overtime, 2) }}</td>
                <td>{{ number_format($r->undertime, 2) }}</td>
                <td>{{ $r->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total Hours:</strong> {{ number_format($totalHours, 2) }} hrs</p>
        <p><strong>Overtime:</strong> {{ number_format($totalOvertime, 2) }} hrs</p>
        <p><strong>Undertime:</strong> {{ number_format($totalUndertime, 2) }} hrs</p>
    </div>
</body>
</html>