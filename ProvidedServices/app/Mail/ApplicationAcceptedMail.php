<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $provider;
    public $jobPost;

    /**
     * Create a new message instance.
     *
     * @param $application
     */
    public function __construct($provider, $jobPost)
    {
        $this->provider = $provider;
        $this->jobPost = $jobPost;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Votre candidature a été acceptée')
                    ->view('emails.application_accepted')
                    ->with([
                        'provider' => $this->provider,
                        'jobPost' => $this->jobPost,
                    ]);
    }
}
