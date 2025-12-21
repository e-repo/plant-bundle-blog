<?php

declare(strict_types=1);

namespace Service\Application\Menu\Query\GetMenu;

final readonly class Query
{
    public function __construct(
        public string $name
    ) {}
}
