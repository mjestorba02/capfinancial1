<!DOCTYPE html>
<html>
<head>
    <title>Journal Entries</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
        .filters { margin-top: 8px; }
        .filters p { margin: 2px 0; }
    </style>
</head>
<body>
    <h2>Journal Entries</h2>

    <div class="filters">
        @if(!empty($filters['account']))
            <p>Account Filter: <strong>{{ $filters['account'] }}</strong></p>
        @endif
        @if(!empty($filters['from']) || !empty($filters['to']))
            <p>Date Range:
                <strong>{{ $filters['from'] ?? '-' }}</strong>
                to
                <strong>{{ $filters['to'] ?? '-' }}</strong>
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th style="text-align:right;">Credit</th>
                <th style="text-align:right;">Debit</th>
                <th>Description</th>
                <th>Date</th>
                <th>Source Module</th>
                <th>Reference ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journals as $j)
                <tr>
                    <td>{{ $j->account }}</td>
                    <td style="text-align:right;">{{ number_format($j->credit ?? 0, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($j->debit ?? 0, 2) }}</td>
                    <td>{{ $j->description ?? '-' }}</td>
                    <td>{{ $j->entry_date }}</td>
                    <td>{{ $j->source_module ?? '-' }}</td>
                    <td>{{ $j->reference_id ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
