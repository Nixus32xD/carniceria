<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockMail extends Mailable
{
    use Queueable, SerializesModels;


    public $products;
    /**
     * Create a new message instance.
     */
    public function __construct(Collection $products)
    {
        $this->products = $products;
    }

    /**
     * Get the message envelope.
     */

    public function build()
    {
        return $this->subject('Notificación de Bajo Stock')
                    ->view('emails.lowstock')
                    ->with(['products' => $this->products]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Por favor reponer el stock de los siguientes productos',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.lowstock',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
