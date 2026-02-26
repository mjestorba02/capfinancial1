<!DOCTYPE html>
<html>
<head>
    <title>Chart of Accounts</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
        .totals { margin-top: 12px; }
        .totals p { margin: 2px 0; }
    </style>
</head>
<body>
    <h2>Chart of Accounts</h2>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Account Type</th>
                <th>Category</th>
                <th>Description</th>
                <th style="text-align:right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $a)
                <tr>
                    <td>{{ $a->account_code }}</td>
                    <td>{{ $a->account_name }}</td>
                    <td>{{ $a->account_type }}</td>
                    <td>{{ $a->category }}</td>
                    <td>{{ $a->description ?? '-' }}</td>
                    <td style="text-align:right;">{{ number_format($a->balance ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p>Total Assets: <strong>{{ number_format($totalAssets ?? 0, 2) }}</strong></p>
        <p>Total Liabilities: <strong>{{ number_format($totalLiabilities ?? 0, 2) }}</strong></p>
        <p>Total Equity: <strong>{{ number_format($totalEquity ?? 0, 2) }}</strong></p>
    </div>
</body>
</html>
