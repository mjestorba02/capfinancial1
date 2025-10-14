<!DOCTYPE html>
<html>
<head>
    <title>Paid Collections Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Accounts Receivable — Paid Collections</h2>
    <table>
        <thead>
            <tr>
                <th>Collection ID</th>
                <th>Customer</th>
                <th>Invoice</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($collections as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->customer_name }}</td>
                    <td>{{ $c->invoice_number }}</td>
                    <td>{{ number_format($c->amount_paid, 2) }}</td>
                    <td>{{ $c->payment_date }}</td>
                    <td>{{ $c->status }}</td>
                    <td>{{ $c->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>