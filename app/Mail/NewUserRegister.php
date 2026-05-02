<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class NewUserRegister extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
    */

    public $mailData;
    public $filePath;

    public function __construct($mailData, $filePath = null)
    {
        $this->mailData = $mailData;    
        $this->filePath = $filePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData['subject'],
            from: new Address('support@gmail.com', $this->mailData['subject']),

        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.newUserRegister',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    */
    public function attachments(): array
    {
        if ($this->filePath) {
            return [
                Attachment::fromPath($this->filePath)
                    ->as('order-invoice.pdf')
                    ->withMime('application/pdf'),
            ];  
        }
        
        return [];
    }
}
