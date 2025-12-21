<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Specification\Category;

use Blog\Domain\Post\Entity\Category;
use Blog\Domain\Post\Repository\CategoryRepositoryInterface;
use CoreKit\Domain\Entity\SpecificationInterface;

final readonly class UniqueName implements SpecificationInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * @param Category $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $category = $this->categoryRepository->findByName(
            name: $candidate->getName()
        );

        return null === $category;
    }
}
