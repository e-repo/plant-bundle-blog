<?php

declare(strict_types=1);

namespace Auth\Domain\User\Dto;

use DateTimeImmutable;

final readonly class UserDto
{
    public function __construct(
        public string $id,
        public string $firstName,
        public ?string $lastName,
        public string $email,
        public string $passwordHash,
        public string $role,
        public string $status,
        public DateTimeImmutable $createdAt,
    ) {}
}
