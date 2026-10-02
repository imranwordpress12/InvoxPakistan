<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').' – Account Created Successfully',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.company-welcome');
    }
}
