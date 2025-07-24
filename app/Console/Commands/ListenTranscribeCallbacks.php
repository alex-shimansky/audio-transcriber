<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\Sqs\SqsClient;
use App\Jobs\ProcessTranscribeCallback;
use Illuminate\Support\Facades\Log;

class ListenTranscribeCallbacks extends Command
{
    protected $signature = 'sqs:listen-transcribe';
    protected $description = 'Listen to transcribe-callback-queue and dispatch ProcessTranscribeCallback job';

    public function handle()
    {
        $sqs = new SqsClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $queueUrl = env('SQS_TRANSCRIBE_CALLBACK_QUEUE');

        $this->info("Listening to SQS: $queueUrl");

        while (true) {
            $result = $sqs->receiveMessage([
                'QueueUrl' => $queueUrl,
                'MaxNumberOfMessages' => 1,
                'WaitTimeSeconds' => 10,
            ]);

            if (!empty($result['Messages'])) {
                foreach ($result['Messages'] as $message) {
                    $body = json_decode($message['Body'], true);

                    // Если сообщение пришло от SNS (в обёртке):
                    if (isset($body['Message'])) {
                        $snsMessage = $body['Message'];
                    } else {
                        // Иначе — это прямой Transcribe JSON
                        $snsMessage = $body;
                    }

                    Log::info('snsMessage: ' . $snsMessage);

                    dispatch(new ProcessTranscribeCallback($snsMessage));

                    $sqs->deleteMessage([
                        'QueueUrl' => $queueUrl,
                        'ReceiptHandle' => $message['ReceiptHandle'],
                    ]);

                    $this->info('Processed Transcribe callback.');
                }
            }
        }
    }
}
