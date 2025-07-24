<?php

namespace App\Jobs;

use App\Services\NotificationService;
use App\Services\SqsPayloadParser;
use App\Services\TranscriptExtractor;
use App\Services\TranscriptionFileFetcher;
use App\Services\TranscriptionUpdater;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTranscribeCallback implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public string $transcribeResponse;

    public function __construct(string $transcribeResponse)
    {
        $this->transcribeResponse = $transcribeResponse;
    }

    public function handle(
        SqsPayloadParser $parser,
        TranscriptionFileFetcher $fetcher,
        TranscriptExtractor $extractor,
        TranscriptionUpdater $updater,
        NotificationService $notifier,
    ): void
    {
        Log::info('--- SQS Job Started ---');

        $sqsData = $parser->parse($this->transcribeResponse);
        if (!$sqsData) return;

        $json = $fetcher->fetch($sqsData['jsonS3Key']);
        if (!$json) return;

        $text = $extractor->extract($json);
        if (!$text) return;

        $transcription = $updater->update($sqsData['s3Key'], $text);
        if (!$transcription) return;

        $notifier->notify($transcription, $text);
    }
}
