<!DOCTYPE html>
<html>
<head>
    <title>Accounts Payable Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #555; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Accounts Payable Report</h2>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Vendor</th>
                <th>Invoice</th>
                <th>Amount</th>
                <th>Mode of Payment</th>
                <th>Due Date</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payables as $p)
                <tr>
                    <td>{{ $p->payment_id }}</td>
                    <td>{{ $p->vendor }}</td>
                    <td>{{ $p->invoice_number }}</td>
                    <td>{{ number_format($p->amount, 2) }}</td>
                    <td>{{ $p->mode_of_payment ?? '-' }}</td>
                    <td>{{ $p->due_date }}</td>
                    <td>{{ $p->payment_date ?? '-' }}</td>
                    <td>{{ $p->status }}</td>
                    <td>{{ $p->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>