<?php

declare(strict_types=1);

namespace CoreKit\Domain\Repository;

use CoreKit\Domain\Entity\FileMetadata;

interface FileMetadataRepositoryInterface
{
    public function add(FileMetadata $entity): void;

    public function remove(FileMetadata $entity): void;

    public function findByFileKey($key): ?FileMetadata;
}
