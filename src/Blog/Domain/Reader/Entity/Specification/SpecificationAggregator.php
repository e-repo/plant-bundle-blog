<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity\Specification;

final readonly class SpecificationAggregator
{
    public function __construct(
        public UniqueEmail $uniqueEmailSpecification,
    ) {}
}
