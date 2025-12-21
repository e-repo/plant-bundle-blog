<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\GetOne\Response;

final readonly class MetadataResponse
{
    public function __construct(
        public ?string $keyword,
        public ?string $description,
    ) {}
}
