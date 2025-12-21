<?php

declare(strict_types=1);

namespace CoreKit\Domain\Service\FileStorage;

use DateTimeInterface;

interface StorageClientInterface
{
    public function upload(mixed $resource, string $location): void;

    /** @return resource */
    public function download(string $location);

    public function delete(string $location): void;

    public function publicUrl(string $location, array $config = []): string;

    public function temporaryUrl(string $location, DateTimeInterface $expiresAt, array $config = []): string;
}
