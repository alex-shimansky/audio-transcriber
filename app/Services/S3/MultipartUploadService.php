<?php

namespace App\Services\S3;

use Aws\Result;

class MultipartUploadService
{
    public function __construct(protected S3ClientFactory $factory)
    {
    }

    public function initiate(string $key, string $contentType): Result
    {
        $client = $this->factory->make();
        return $client->createMultipartUpload([
            'Bucket' => $this->factory->getBucket(),
            'Key' => $key,
            'ContentType' => $contentType,
        ]);
    }

    public function signPart(string $key, string $uploadId, int $partNumber): string
    {
        $client = $this->factory->make();
        $command = $client->getCommand('UploadPart', [
            'Bucket' => $this->factory->getBucket(),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        return (string)$client->createPresignedRequest($command, '+10 minutes')->getUri();
    }

    public function complete(string $key, string $uploadId, array $parts): Result
    {
        $client = $this->factory->make();
        return $client->completeMultipartUpload([
            'Bucket' => $this->factory->getBucket(),
            'Key' => $key,
            'UploadId' => $uploadId,
            'MultipartUpload' => ['Parts' => $parts],
        ]);
    }

    public function abort(string $key, string $uploadId): void
    {
        $client = $this->factory->make();
        $client->abortMultipartUpload([
            'Bucket' => $this->factory->getBucket(),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);
    }
}
