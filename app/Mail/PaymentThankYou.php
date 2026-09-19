<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PaymentThankYou extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thank you for your payment');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-thank-you');
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return $this->transaction->payment_screenshot
            ? [Attachment::fromStorageDisk('local', $this->transaction->payment_screenshot)]
            : [];
    }
}