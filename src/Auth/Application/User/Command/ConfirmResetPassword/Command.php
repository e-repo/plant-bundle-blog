<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ConfirmResetPassword;

final readonly class Command
{
    public function __construct(
        public string $token,
        public string $password
    ) {}
}
