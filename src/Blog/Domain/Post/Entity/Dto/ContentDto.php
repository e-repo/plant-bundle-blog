<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Dto;

final readonly class ContentDto
{
    public function __construct(
        public string $text,
        public array $images = [],
    ) {}
}
