<?php

declare(strict_types=1);

namespace Blog\Application\Post\Category\Query\GetOne;

use Blog\Domain\Post\Entity\Dto\CategoryDto;
use Blog\Domain\Post\Fetcher\CategoryFetcherInterface;
use CoreKit\Application\Bus\QueryHandlerInterface;
use DomainException;

final readonly class Handler implements QueryHandlerInterface
{
    public function __construct(
        private CategoryFetcherInterface $categoryFetcher,
    ) {}

    public function __invoke(Query $query): CategoryDto
    {
        $category = $this->categoryFetcher->findById($query->id);

        if (null === $category) {
            throw new DomainException(
                sprintf("Категория по идентификатору '%s' не найдена", $query->id)
            );
        }

        return $category;
    }
}
