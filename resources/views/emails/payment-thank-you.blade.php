<!DOCTYPE html>
<html lang="en">
<body>
    <p>Dear {{ $transaction->company->name }},</p>

    <p>Thank you for your payment. We have successfully received and confirmed transaction {{ $transaction->invoice_number }}.</p>

    <p>Amount paid: PKR {{ number_format((float) $transaction->amount, 2) }}</p>

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>