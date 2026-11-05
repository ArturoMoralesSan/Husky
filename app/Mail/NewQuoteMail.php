<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public string $quoteUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva cotización recibida | Industrias Husky',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-quotes',
            with: [
                'quote' => $this->quote,
                'quoteUrl' => $this->quoteUrl,
                'logoUrl' => url('/images/husky_blanco.webp'),
            ],
        );
    }
}