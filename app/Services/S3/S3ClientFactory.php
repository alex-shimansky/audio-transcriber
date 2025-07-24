<?php

namespace App\Services\S3;

use Aws\S3\S3Client;

class S3ClientFactory
{
    public function make(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    public function getBucket(): string
    {
        return config('filesystems.disks.s3.bucket');
    }
}
