<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use App\Models\Release;
use Illuminate\Http\Request;

class artistSubmittedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $catalog;
    /**
     * Create a new message instance.
     */
    public function __construct(public Release $release, public Release $release_before)
    {
        $this->catalog = $release->catalog;
        $release->toArray();
        $release_before->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: 'nicolas@blizzardaudioclub.ch',
            //cc: 'etienne@blizzardaudioclub.ch',
            from: new Address('info@blizzardaudioclub.ch', 'Blizzard Audio Club'),
            subject: 'Labelcopy ' .$this->catalog. ' - mise à jour de la sortie !',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.artistsubmitted.notification',
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
