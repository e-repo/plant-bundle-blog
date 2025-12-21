<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\RequestResetPassword;

final readonly class Command
{
    public function __construct(
        public string $email,
        public string $registrationSource,
    ) {}
}
