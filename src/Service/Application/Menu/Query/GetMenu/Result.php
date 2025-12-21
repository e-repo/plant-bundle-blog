<?php

declare(strict_types=1);

namespace Service\Application\Menu\Query\GetMenu;

use Service\Application\Menu\Query\GetMenu\Result\MenuItemDto;

final readonly class Result
{
    /**
     * @param MenuItemDto[] $menuItems
     */
    public function __construct(
        public array $menuItems
    ) {}
}
