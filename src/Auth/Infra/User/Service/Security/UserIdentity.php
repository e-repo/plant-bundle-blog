<?php

declare(strict_types=1);

namespace Auth\Infra\User\Service\Security;

use Auth\Domain\User\Entity\Status;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserIdentity implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $email,
        public string $passwordHash,
        public string $role,
        public string $status,
    ) {}

    public function isEqualTo(UserInterface $user): bool
    {
        if (! $user instanceof self) {
            return false;
        }

        return $this->id === $user->id &&
            $this->email === $user->email &&
            $this->passwordHash === $user->passwordHash &&
            $this->role === $user->role &&
            $this->status === $user->status;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE->value;
    }

    public function eraseCredentials(): void {}

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }
}
