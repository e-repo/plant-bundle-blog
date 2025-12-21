<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity\Event;

use CoreKit\Domain\Event\DomainEventInterface;
use DateTimeImmutable;

final readonly class UserPasswordResetEvent implements DomainEventInterface
{
    public function __construct(
        public string $email,
        public string $resetPasswordToken,
        public DateTimeImmutable $passwordTokenExpires,
        public string $registrationSource,
    ) {}
}
