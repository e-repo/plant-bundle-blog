<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Query\GetOne;

use Blog\Domain\Post\Entity\Status;

final class Result
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $shortTitle,
        public string $content,
        public string $image,
        public Status $status,
        public string $createdAt,
        public ?string $id = null,
        public ?string $categoryName = null,
        public bool $commentAvailable = false,
    ) {}
}
