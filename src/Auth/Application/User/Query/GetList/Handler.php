<?php

declare(strict_types=1);

namespace Auth\Application\User\Query\GetList;

use Auth\Domain\User\Dto\UsersByListFilterDto;
use Auth\Domain\User\Fetcher\ListFilter;
use Auth\Domain\User\Fetcher\UserFetcherInterface;
use CoreKit\Application\Bus\QueryHandlerInterface;

final readonly class Handler implements QueryHandlerInterface
{
    public function __construct(
        private UserFetcherInterface $userFetcher,
    ) {}

    /**
     * @param Query $query
     * @return UsersByListFilterDto
     */
    public function __invoke(Query $query): UsersByListFilterDto
    {
        $filter = new ListFilter(
            firstName: $query->firstName,
            lastName: $query->lastName,
            email: $query->email,
            role: $query->role,
            status: $query->status
        );

        $users = $this->userFetcher
            ->findByListFilter(
                filter: $filter,
                offset: $query->offset,
                limit: $query->limit
            );

        return new UsersByListFilterDto(
            userList: $users,
            total: $this->userFetcher->countByListFilter($filter)
        );
    }
}
