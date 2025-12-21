<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Dto;

use Blog\Domain\Post\Entity\Status;
use DateTimeImmutable;

final readonly class PostDto
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $shortTitle,
        public ContentDto $content,
        public Status $status,
        public ImageDto $image,
        public DateTimeImmutable $createdAt,
        public ?string $id = null,
        public ?string $categoryName = null,
        public bool $commentAvailable = false,
        public ?MetadataDto $meta = null,
    ) {}
}
