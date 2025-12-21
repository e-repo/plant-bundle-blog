<?php

declare(strict_types=1);

namespace Auth\Infra\User\Service\Security;

use Auth\Domain\User\Dto\UserDto;
use Auth\Domain\User\Fetcher\UserFetcherInterface;
use Doctrine\DBAL\Exception;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class UserProvider implements UserProviderInterface
{
    public function __construct(
        private UserFetcherInterface $userFetcher
    ) {}

    /**
     * @throws Exception
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (! $user instanceof UserIdentity) {
            throw new UnsupportedUserException(
                sprintf('Пользователь не соответствует %s.', get_class($user))
            );
        }

        $loadedUser = $this->getUserByUserIdentifier($user->getUserIdentifier());

        return $this->makeUserIdentity($loadedUser);
    }

    public function supportsClass(string $class): bool
    {
        return $class === UserIdentity::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $loadedUser = $this->getUserByUserIdentifier($identifier);

        return $this->makeUserIdentity($loadedUser);
    }

    private function makeUserIdentity(UserDto $user): UserIdentity
    {
        return new UserIdentity(
            id: $user->id,
            firstName: $user->firstName,
            email: $user->email,
            passwordHash: $user->passwordHash,
            role: $user->role,
            status: $user->status,
        );
    }

    private function getUserByUserIdentifier(string $user): UserDto
    {
        $loadedUser = $this->userFetcher->findByEmail($user);

        if (null === $loadedUser) {
            throw new UserNotFoundException('Пользователь не найден.');
        }

        return $loadedUser;
    }
}
