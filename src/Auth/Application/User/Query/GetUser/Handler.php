<?php

declare(strict_types=1);

namespace Auth\Application\User\Query\GetUser;

use Auth\Domain\User\Dto\UserProfileDto;
use Auth\Domain\User\Fetcher\UserFetcherInterface;
use CoreKit\Application\Bus\QueryHandlerInterface;
use DomainException;

final readonly class Handler implements QueryHandlerInterface
{
    public function __construct(
        private UserFetcherInterface $userFetcher,
    ) {}

    public function __invoke(Query $query): UserProfileDto
    {
        $user = $this->userFetcher->findById($query->userId);

        if (null === $user) {
            throw new DomainException(
                sprintf("Пользователь по идентификатору '%s' не найден", $query->userId)
            );
        }

        return $user;
    }
}
