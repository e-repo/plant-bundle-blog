<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity;

final readonly class ReaderDto
{
    public function __construct(
        public string $firstname,
        public ?string $lastname,
        public string $email,
        public ?string $id = null,
    ) {}
}
