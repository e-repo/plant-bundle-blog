<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Dto;

use Blog\Domain\Post\Entity\ImageType;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use SplFileInfo;

final class ImageDto
{
    public function __construct(
        public readonly string $originalFileName,
        public readonly Id $fileKey,
        public readonly ImageType $type,
        public readonly ?SplFileInfo $file = null,
        public readonly ?string $extension = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public ?string $url = null,
    ) {}

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
