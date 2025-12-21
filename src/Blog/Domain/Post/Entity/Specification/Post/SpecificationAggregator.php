<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Specification\Post;

final readonly class SpecificationAggregator
{
    public function __construct(
        public UniqueShortTitle $uniqueShortTitle,
        public UniqueTitle $uniqueTitle,
    ) {}
}
