<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Receipt {{ $collection->invoice_number ?? $collection->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .wrapper {
            max-width: 700px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 6px;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            padding: 6px 8px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f5f5f5;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .small {
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Collection Receipt</h1>
            <p class="small">Receipt for collection {{ $collection->invoice_number ?? $collection->id }}</p>
        </div>

        <div>
            <div class="section-title">General Information</div>
            <table>
                <tr>
                    <th style="width: 35%;">Customer</th>
                    <td>{{ $collection->customer_name }}</td>
                </tr>
                <tr>
                    <th>Invoice / Receipt No.</th>
                    <td>{{ $collection->invoice_number ?? $collection->id }}</td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td>{{ $collection->remarks ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td>{{ $collection->payment_date ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $collection->status }}</td>
                </tr>
            </table>
        </div>

        <div>
            <div class="section-title">Amounts</div>
            <table>
                <tr>
                    <th style="width: 35%;">Amount Due</th>
                    <td class="text-right">₱{{ number_format($collection->amount_due, 2) }}</td>
                </tr>
                <tr>
                    <th>Amount Paid</th>
                    <td class="text-right">₱{{ number_format($collection->amount_paid, 2) }}</td>
                </tr>
            </table>
        </div>

        <p class="small">
            This is a system-generated PDF receipt for this collection record.
        </p>
    </div>
</body>
</html>

