<!DOCTYPE html>
<html lang="en">
<body>
    <p>Dear {{ $transaction->company->name }},</p>

    <p>
        Your subscription payment of <strong>PKR {{ number_format((float) $transaction->amount, 2) }}</strong>
        for invoice <strong>{{ $transaction->invoice_number }}</strong> is now <strong>DUE</strong>.
    </p>

    <p>
        <strong>Invoice:</strong> {{ $transaction->invoice_number }}<br>
        <strong>Plan:</strong> {{ ucfirst($transaction->subscription_type) }}<br>
        <strong>Amount Due:</strong> PKR {{ number_format((float) $transaction->amount, 2) }}<br>
        <strong>Due Date:</strong> {{ $transaction->due_at ? $transaction->due_at->format('d-M-Y') : 'Today' }}
    </p>

    <p>Please complete your payment to ensure uninterrupted service.</p>

    <p>Best regards,<br><strong>{{ config('app.name') }} Team</strong></p>
</body>
</html>
