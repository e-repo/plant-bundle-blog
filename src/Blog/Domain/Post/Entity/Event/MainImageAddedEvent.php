<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Event;

use Blog\Domain\Post\Entity\ImageType;
use CoreKit\Domain\Entity\Id;
use CoreKit\Domain\Event\DomainEventInterface;

final readonly class MainImageAddedEvent implements DomainEventInterface
{
    public function __construct(
        public Id $postId,
        public ImageType $type,
        public string $fileKey,
        public string $extension,
        public string $originalFileName,
    ) {}
}
