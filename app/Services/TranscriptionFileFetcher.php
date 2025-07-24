<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscriptionFileFetcher
{
    public function fetch(string $jsonS3Key): ?array
    {
        try {
            $jsonContent = Storage::disk('s3')->get($jsonS3Key);
            Log::info("Json: $jsonContent");
            return json_decode($jsonContent, true);
        } catch (\Throwable $e) {
            Log::error("Failed to read transcription file from S3: " . $e->getMessage());
            return null;
        }
    }
}
