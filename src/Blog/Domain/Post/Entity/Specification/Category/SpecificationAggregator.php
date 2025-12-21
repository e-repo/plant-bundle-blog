<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Specification\Category;

final readonly class SpecificationAggregator
{
    public function __construct(
        public UniqueName $uniqueNameSpecification,
    ) {}
}
