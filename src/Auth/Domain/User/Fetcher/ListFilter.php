<?php

declare(strict_types=1);

namespace Auth\Domain\User\Fetcher;

final class ListFilter
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $role,
        public ?string $status,
    ) {}
}
