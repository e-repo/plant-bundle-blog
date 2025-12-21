<?php

declare(strict_types=1);

namespace Auth\Application\User\Query\GetUser;

final readonly class Query
{
    public function __construct(
        public string $userId,
    ) {}
}
