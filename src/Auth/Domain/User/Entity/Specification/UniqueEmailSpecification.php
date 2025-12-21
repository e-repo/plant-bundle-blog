<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity\Specification;

use Auth\Domain\User\Entity\User;
use Auth\Domain\User\Repository\UserRepositoryInterface;

final readonly class UniqueEmailSpecification
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function isSatisfiedBy(User $user): bool
    {
        $user = $this->userRepository->findByEmail(
            $user->getEmail()->value
        );

        return null === $user;
    }
}
