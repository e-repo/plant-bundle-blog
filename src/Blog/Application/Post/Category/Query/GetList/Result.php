<?php

declare(strict_types=1);

namespace Blog\Application\Post\Category\Query\GetList;

use Blog\Domain\Post\Entity\Dto\CategoryDto;

final readonly class Result
{
    /**
     * @param CategoryDto[] $categories
     */
    public function __construct(
        public array $categories,
        public int $totalCount,
    ) {}
}
