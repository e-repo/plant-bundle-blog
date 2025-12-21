<?php

declare(strict_types=1);

namespace Blog\Application\Post\Category\Command\Create;

use Blog\Domain\Post\Entity\Category;
use Blog\Domain\Post\Entity\Dto\CategoryDto;
use Blog\Domain\Post\Entity\Specification\Category\SpecificationAggregator;
use Blog\Domain\Post\Repository\CategoryRepositoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private SpecificationAggregator $specificationAggregator,
    ) {}

    public function __invoke(Command $command): void
    {
        $category = new Category(
            categoryDto: new CategoryDto(
                name: $command->name,
                description: $command->description,
            ),
            specificationAggregator: $this->specificationAggregator,
        );

        $this->repository->add($category);
    }
}
