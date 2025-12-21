<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\Create\Command;

final readonly class MetaCommand
{
    public function __construct(
        public ?string $keyword = null,
        public ?string $description = null,
    ) {}
}
