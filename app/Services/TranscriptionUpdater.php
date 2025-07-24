<?php

namespace App\Services;

use App\Enums\TranscriptionStatus;
use App\Models\Transcription;
use Illuminate\Support\Facades\Log;

class TranscriptionUpdater
{
    public function update(string $s3Key, string $text): ?Transcription
    {
        $transcription = Transcription::where('s3_key', $s3Key)->first();

        if (!$transcription) {
            Log::error("Transcription not found for s3_key: $s3Key");
            return null;
        }

        $transcription->update([
            'transcription_text' => $text,
            'status' => TranscriptionStatus::Done,
        ]);

        Log::info("Transcription updated for s3_key: $s3Key");

        return $transcription;
    }
}
