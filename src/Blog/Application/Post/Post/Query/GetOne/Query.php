<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Query\GetOne;

final readonly class Query
{
    public function __construct(
        public string $id,
    ) {}
}
