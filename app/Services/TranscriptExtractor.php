<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TranscriptExtractor
{
    public function extract(array $json): ?string
    {
        $transcriptText = $json['results']['transcripts'][0]['transcript'] ?? null;

        if (!$transcriptText) {
            Log::error('Transcript text missing in JSON file');
            return null;
        }

        return $transcriptText;
    }
}
