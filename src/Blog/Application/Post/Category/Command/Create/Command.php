<?php

declare(strict_types=1);

namespace Blog\Application\Post\Category\Command\Create;

final readonly class Command
{
    public function __construct(
        public string $name,
        public string $description,
    ) {}
}
