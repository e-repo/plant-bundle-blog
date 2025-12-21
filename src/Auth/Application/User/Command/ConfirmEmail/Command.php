<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ConfirmEmail;

final readonly class Command
{
    public function __construct(
        public string $userId,
        public string $token
    ) {}
}
