<?php

declare(strict_types=1);

namespace Auth\Application\User\Query\GetList;

final class Query
{
    public function __construct(
        public int $offset,
        public int $limit,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $role,
        public ?string $status,
    ) {}
}
