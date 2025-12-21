<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Repository;

use Blog\Domain\Post\Entity\Category;

interface CategoryRepositoryInterface
{
    public function add(Category $category): void;

    public function findByName(string $name): ?Category;

    public function findById(string $id): ?Category;
}
