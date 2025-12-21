<?php

declare(strict_types=1);

namespace Auth\Domain\User\Fetcher;

use Auth\Domain\User\Dto\UserDto;
use Auth\Domain\User\Dto\UserProfileDto;

interface UserFetcherInterface
{
    public function findByEmail(string $email): ?UserDto;

    public function findById(string $id): ?UserProfileDto;

    /**
     * @param ListFilter $filter
     * @param int $offset
     * @param int $limit
     * @return UserProfileDto[]
     */
    public function findByListFilter(ListFilter $filter, int $offset, int $limit): array;

    public function countByListFilter(ListFilter $filter): int;
}
