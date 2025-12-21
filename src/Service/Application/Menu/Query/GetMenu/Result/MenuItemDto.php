<?php

declare(strict_types=1);

namespace Service\Application\Menu\Query\GetMenu\Result;

final readonly class MenuItemDto
{
    public function __construct(
        public string $id,
        public string $title,
        public string $icon,
    ) {}
}
