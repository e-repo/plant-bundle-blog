<?php

declare(strict_types=1);

namespace CoreKit\Infra\FileStorage;

use CoreKit\Domain\Service\FileStorage\StorageClientInterface;
use DateTimeInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

readonly class S3StorageClient implements StorageClientInterface
{
    public function __construct(
        private FilesystemOperator $s3FileSystem,
        private string $bucket,
        private string $publicUrl
    ) {}

    /**
     * @param resource $resource
     * @throws FilesystemException
     */
    public function upload(mixed $resource, string $location): void
    {
        $this->s3FileSystem->writeStream(
            location: $location,
            contents: $resource,
        );
    }

    /**
     * @throws FilesystemException
     * @return resource
     */
    public function download(string $location): mixed
    {
        return $this->s3FileSystem->readStream($location);
    }

    /**
     * @throws FilesystemException
     */
    public function delete(string $location): void
    {
        $this->s3FileSystem->delete(
            location: $location
        );
    }

    public function publicUrl(string $location, array $config = []): string
    {
        return sprintf('%s/%s/%s', $this->publicUrl, $this->bucket, $location);
    }

    public function temporaryUrl(string $location, DateTimeInterface $expiresAt, array $config = []): string
    {
        return $this->s3FileSystem->temporaryUrl($location, $expiresAt, $config);
    }
}
