<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobseekerToEmployerRemonderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
    */
    public $employer, $content, $jobseeker;
    public function __construct($employer, $content, $jobseeker)
    {
        $this->employer = $employer;
        $this->content = $content;
        $this->jobseeker = $jobseeker;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Following Up on My Job Application',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.jobseeker-to-employer-remonder',
            with: [
                'name' => $this->employer,
                'otp' => $this->content,
                'content' => $this->jobseeker,
            ],
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
