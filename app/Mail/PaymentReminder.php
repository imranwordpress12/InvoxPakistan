<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Transaction $transaction,
        public Customer $customer,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder – Invoice '.$this->transaction->invoice_number.' Due in 7 Days',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-reminder');
    }
}
