<?php

namespace App\Http\Controllers;

use App\Enums\TranscriptionStatus;
use App\Http\Requests\AbortS3MultipartUploadRequest;
use App\Http\Requests\CompleteS3MultipartUploadRequest;
use App\Http\Requests\InitiateS3MultipartUploadRequest;
use App\Http\Requests\SignPartS3MultipartUploadRequest;
use App\Models\Transcription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\S3\S3ClientFactory;
use App\Services\S3\MultipartUploadService;
use App\Services\Sqs\TranscriptionQueueService;

class S3MultipartController extends Controller
{
    public function __construct(
        protected S3ClientFactory $clientFactory,
        protected MultipartUploadService $uploadService,
        protected TranscriptionQueueService $queueService
    ) {}

    public function initiate(InitiateS3MultipartUploadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $extension = pathinfo($validated['filename'], PATHINFO_EXTENSION);
        $uuid = (string) Str::uuid();
        $s3Key = "uploads/{$uuid}.{$extension}";
        $contentType = $validated['contentType'] ?? 'application/octet-stream';

        $transcription = Transcription::create([
            'email' => $validated['email'] ?? null,
            's3_key' => $s3Key,
            'status' => TranscriptionStatus::Pending,
        ]);

        $result = $this->uploadService->initiate($s3Key, $contentType);

        Log::info('Multipart upload initiated', [
            'uploadId' => $result['UploadId'],
            'key' => $s3Key,
            'transcription_id' => $transcription->id,
        ]);

        return response()->json([
            'uploadId' => $result['UploadId'],
            'key' => $s3Key,
            'bucket' => $this->clientFactory->getBucket(),
            'transcription_id' => $transcription->id,
            'status_url' => route('transcription.status', $transcription->id),
        ]);
    }

    public function signPart(SignPartS3MultipartUploadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $url = $this->uploadService->signPart($validated['key'], $validated['uploadId'], $validated['partNumber']);

        return response()->json(['url' => $url]);
    }

    public function complete(CompleteS3MultipartUploadRequest $request): JsonResponse
    {
        Log::info('Complete multipart upload data', $request->all());

        $validated = $request->validated();

        $parts = collect($validated['parts'])->map(fn($part) => [
            'PartNumber' => $part['PartNumber'],
            'ETag' => $part['etag'] ?? $part['ETag'],
        ])->toArray();

        $result = $this->uploadService->complete($validated['key'], $validated['uploadId'], $parts);

        Log::info('Multipart upload completed', [
            'location' => $result['Location'],
            'key' => $request->key,
        ]);

        $transcription = Transcription::where('s3_key', $validated['key'])->first();
        if ($transcription) {
            try {
                $this->queueService->send([
                    'transcription_id' => $transcription->id,
                    's3_key' => $transcription->s3_key,
                    'email' => $transcription->email,
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to send to SQS: {$e->getMessage()}");
                $transcription->update(['status' => TranscriptionStatus::Error]);
            }
        }

        return response()->json(['location' => $result['Location']]);
    }

    public function abort(AbortS3MultipartUploadRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $this->uploadService->abort($validated['key'], $validated['uploadId']);

            Log::info('Multipart upload aborted', [
                'uploadId' => $request->uploadId,
                'key' => $request->key,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to abort multipart upload', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'aborted']);
    }
}
