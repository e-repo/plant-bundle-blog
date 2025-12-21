<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Event;

use Blog\Domain\Post\Entity\Status;
use CoreKit\Domain\Event\DomainEventInterface;
use DateTimeImmutable;

final readonly class PostCreatedEvent implements DomainEventInterface
{
    public function __construct(
        public string $id,
        public string $slug,
        public string $title,
        public Status $status,
        public string $categoryId,
        public string $categoryName,
        public DateTimeImmutable $createdAt,
    ) {}
}
