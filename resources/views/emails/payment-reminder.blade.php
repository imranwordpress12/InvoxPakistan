<!DOCTYPE html>
<html lang="en">
<body>
    <p>Hello {{ $customer->business_name }},</p>

    <p>
        This is a reminder that your subscription payment of
        <strong>PKR {{ number_format((float) $transaction->amount, 2) }}</strong>
        is due on <strong>{{ $transaction->due_at->format('d-M-Y') }}</strong>.
    </p>

    <p>
        <strong>Invoice:</strong> {{ $transaction->invoice_number }}<br>
        <strong>Plan:</strong> {{ ucfirst($transaction->subscription_type) }}<br>
        <strong>Amount Due:</strong> PKR {{ number_format((float) $transaction->amount, 2) }}<br>
        <strong>Due Date:</strong> {{ $transaction->due_at->format('d-M-Y') }}
    </p>

    <p>Please make your payment before the due date to keep your subscription active.</p>

    <p>Thank you,<br><strong>Invox Pakistan</strong></p>
</body>
</html>
