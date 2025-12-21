<?php

declare(strict_types=1);

namespace Blog\Application\Reader\Command\CreateOrUpdate;

final readonly class Command
{
    public function __construct(
        public string $id,
        public string $firstname,
        public ?string $lastname,
        public string $email,
    ) {}
}
