<?php

declare(strict_types=1);

namespace Service\Application\Menu\Query\GetMenu;

use CoreKit\Application\Bus\QueryHandlerInterface;
use CoreKit\Domain\Exception\NotFoundException;
use Service\Application\Menu\Query\GetMenu\Result\MenuItemDto;

final readonly class Handler implements QueryHandlerInterface
{
    public function __construct(
        private array $menuList,
    ) {}

    public function __invoke(Query $query): Result
    {
        if (! isset($this->menuList[$query->name])) {
            throw new NotFoundException(sprintf('Меню \'%s\' не найдено', $query->name));
        }

        return new Result(
            menuItems: array_map($this->toMenuItem(...), $this->menuList[$query->name])
        );
    }

    private function toMenuItem(array $menuItem): MenuItemDto
    {
        return new MenuItemDto(
            id: $menuItem['id'],
            title: $menuItem['title'],
            icon: $menuItem['icon'],
        );
    }
}
