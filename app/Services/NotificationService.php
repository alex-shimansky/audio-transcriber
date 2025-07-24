<?php

namespace App\Services;

use App\Mail\TranscriptionReadyMail;
use App\Models\Transcription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notify(Transcription $transcription, string $text): void
    {
        if (!$transcription->email) {
            Log::warning("No email found for transcription ID {$transcription->id}");
            return;
        }

        Mail::to($transcription->email)->queue(new TranscriptionReadyMail($text));

        Log::info("Notification email queued to: {$transcription->email}");
    }
}
