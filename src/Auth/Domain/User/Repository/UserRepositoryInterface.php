<?php

declare(strict_types=1);

namespace Auth\Domain\User\Repository;

use Auth\Domain\User\Entity\User;

interface UserRepositoryInterface
{
    public function add(User $user): void;

    public function findByEmail(string $email): ?User;

    public function findByResetPasswordToken(string $token): ?User;

    /**
     * @return User[]
     */
    public function getIterator(): iterable;
}
