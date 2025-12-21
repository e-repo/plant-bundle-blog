<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\GetOne\Response;

final readonly class ImageResponse
{
    public function __construct(
        public ?string $main,
        public array $content,
    ) {}
}
