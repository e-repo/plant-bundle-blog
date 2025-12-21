<?php

declare(strict_types=1);

namespace Auth\Domain\User\Dto;

use DateTimeImmutable;

final readonly class UserProfileDto
{
    public function __construct(
        public string $id,
        public string $firstName,
        public ?string $lastName,
        public string $email,
        public string $role,
        public string $status,
        public ?string $registrationSource,
        public DateTimeImmutable $createdAt,
    ) {}
}
