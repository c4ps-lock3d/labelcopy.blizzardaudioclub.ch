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
    public array $release_before_array;
    /**
     * Create a new message instance.
     */
    public function __construct(public Release $release, public array $release_before)
    {
        $this->catalog = $release->catalog;
        $this->release_before_array = $release_before;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@blizzardaudioclub.ch', 'Blizzard Audio Club'),
            to: [config('mail.to_etienne'), config('mail.to_nicolas')],
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
            with: [
                'release' => $this->release,
                'release_before' => $this->release_before_array
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
        return [];
    }
}
