<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SqsPayloadParser
{
    public function parse(string $transcribeResponse): ?array
    {
        $transcribeResponseArray = json_decode($transcribeResponse, true);
        Log::info('data: ', $transcribeResponseArray);

        if (!isset($transcribeResponseArray['detail']['TranscriptionJobName'])) {
            Log::error('No TranscriptionJobName in payload');
            return null;
        }

        $jobName = $transcribeResponseArray['detail']['TranscriptionJobName'];

        $s3Key = str_replace('_', '/', str_replace('transcribe-', '', $jobName));
        Log::info('$s3Key: ' . $s3Key);

        $jsonS3Key = 'transcriptions/' . $jobName . '.json';
        Log::info("jsonS3Key: $jsonS3Key");

        return [
            's3Key' => $s3Key,
            'jsonS3Key' => $jsonS3Key,
        ];
    }
}
