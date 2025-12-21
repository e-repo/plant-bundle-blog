<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\GetOne;

use Blog\UI\Http\V1\Post\Post\GetOne\Response\ImageResponse;
use Blog\UI\Http\V1\Post\Post\GetOne\Response\MetadataResponse;
use CoreKit\UI\Http\Response\ResponseInterface;
use DateTimeImmutable;

final readonly class Response implements ResponseInterface
{
    public function __construct(
        public string $id,
        public string $title,
        public string $shortTitle,
        public string $slug,
        public string $content,
        public string $status,
        public DateTimeImmutable $createdAt,
        public ImageResponse $image,
        public bool $commentAvailable = false,
        public ?string $categoryName = null,
        public ?MetadataResponse $meta = null,
    ) {}
}
