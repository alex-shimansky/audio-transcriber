<?php

namespace App\Services\Sqs;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Facades\Log;

class TranscriptionQueueService
{
    protected SqsClient $sqs;

    public function __construct()
    {
        $this->sqs = new SqsClient([
            'version' => 'latest',
            'region' => config('services.aws.region'),
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
    }

    public function send(array $payload): void
    {
        $this->sqs->sendMessage([
            'QueueUrl' => config('services.aws.sqs_transcribe_queue_url'),
            'MessageBody' => json_encode($payload),
        ]);

        Log::info("Sent transcription task to SQS", $payload);
    }
}
