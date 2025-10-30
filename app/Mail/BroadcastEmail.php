<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BroadcastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = '';
    public $content  = '';
    public $attachmentsFile  =[];
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $data, $attachments )
    {
        //
        $this->subject = $subject;
        $this->content = $data;
        $this->attachmentsFile = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.broadcast',
            with: [
                'data' => $this->content
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $a = [];
        foreach ($this->attachmentsFile as $attachment) {
            $a[] =  asset("/images/attachments/$attachment");
        }
        return $a;
    }
}
