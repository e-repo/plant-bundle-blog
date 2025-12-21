<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ChangeStatus;

final readonly class Command
{
    public function __construct(
        public string $id,
        public string $status,
        public ?string $changedBy,
    ) {}
}
