<?php

declare(strict_types=1);

namespace Auth\Domain\User\Service\PasswordHasher;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class Hasher
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function hash(PasswordHashedUserInterface $user): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());

        $user->changePlainPassword($hashedPassword);
    }

    public function verify(PasswordHashedUserInterface $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }
}
