<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TranscriptionReadyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $transcriptText;

    public function __construct(string $transcriptText)
    {
        $this->transcriptText = $transcriptText;
    }

    public function build(): self
    {
        return $this->subject('Your transcription is ready')
            ->view('emails.transcription_ready')
            ->with([
                'text' => $this->transcriptText,
            ]);
    }
}
