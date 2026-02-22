<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $order->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 1px solid #ddd; padding-bottom: 12px; }
        .header h1 { margin: 0; font-size: 18px; }
        .meta { color: #666; font-size: 11px; }
        table.info { width: 100%; border-collapse: collapse; margin: 16px 0; }
        table.info td { padding: 6px 0; }
        table.info td:first-child { color: #666; width: 40%; }
        .amount { font-size: 16px; font-weight: bold; margin: 16px 0; }
        .footer { margin-top: 32px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Management System</h1>
        <p class="meta">Transaction Receipt</p>
    </div>
    <h2>Receipt # {{ $order->receipt_number }}</h2>
    <table class="info">
        <tr><td>Date</td><td>{{ $order->created_at->format('F d, Y h:i A') }}</td></tr>
        <tr><td>Budget Request</td><td>{{ $order->budgetRequest->request_id ?? '—' }} — {{ $order->budgetRequest->purpose ?? '—' }}</td></tr>
        <tr><td>Employee</td><td>{{ $order->employee->name ?? '—' }}</td></tr>
        <tr><td>Material / Description</td><td>{{ $order->material_description }}</td></tr>
        <tr><td>Amount</td><td class="amount">₱{{ number_format($order->amount, 2) }}</td></tr>
        @if($order->remarks)
        <tr><td>Remarks</td><td>{{ $order->remarks }}</td></tr>
        @endif
    </table>
    <div class="footer">
        This order is recorded in Accounts Receivable - Collections as Ordered.
    </div>
</body>
</html>
