<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewJobPost extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $full_name;
    public $content;
    public $pwd;
    public $job_id;
    public $job_number;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $email = '',
        $full_name = '',
        $content = '',
        $pwd = '',
        $job_id = null,
        $job_number = null
    ) {
        $this->email = $email;
        $this->full_name = $full_name;
        $this->content = $content;
        $this->pwd = $pwd;
        $this->job_id = $job_id;
        $this->job_number = $job_number;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'New Job Submission - Under Review',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mails.new-jobpost-success',
            with: [
                'email'      => $this->email,
                'full_name'  => $this->full_name,
                'content'    => $this->content,
                'pwd'        => $this->pwd,
                'job_id'     => $this->job_id,
                'job_number' => $this->job_number,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
