<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Dto;

final readonly class MetadataDto
{
    public function __construct(
        public ?string $keyword = null,
        public ?string $description = null,
    ) {}
}
