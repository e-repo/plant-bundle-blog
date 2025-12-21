<?php

declare(strict_types=1);

namespace Test\Integration\Common\User;

use Auth\Domain\User\Entity\Role;
use Auth\Domain\User\Entity\Status;
use Auth\Infra\User\Service\Security\UserIdentity;

final class UserBuilder
{
    private function __construct(
        private string $id,
        private string $firstName,
        private string $email,
        private string $passwordHash,
        private string $role,
        private string $status,
    ) {}

    public static function createAdmin(): self
    {
        return new self(
            id: '125d7cd7-0b77-409a-bc5c-19b44416a5fa',
            firstName: 'TestFirstName_2',
            email: 'test_2@test.ru',
            passwordHash: '$2y$13$ftB8l5tXUdp3aPALxhYAs.21OSP8Kq8ymWEOzctzJydUnvkE7zWZS', // hash: secret_2
            role: Role::ADMIN->value,
            status: Status::ACTIVE->value,
        );
    }

    public static function createUser(): self
    {
        return new self(
            id: '76c3a2d9-49fd-4fbd-a0f4-0022d38dbaba',
            firstName: 'TestFirstName_3',
            email: 'test_3@test.ru',
            passwordHash: '$2y$13$M9SZqESK6wvMVNXoRF7NduGufBIJgOatyZplw.dSVxRvH1Fw/1oju', // hash: secret_3
            role: Role::USER->value,
            status: Status::ACTIVE->value,
        );
    }

    public function build(): UserIdentity
    {
        return new UserIdentity(
            id: $this->id,
            firstName: $this->firstName,
            email: $this->email,
            passwordHash: $this->passwordHash,
            role: $this->role,
            status: $this->status,
        );
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role->value;

        return $this;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status->value;

        return $this;
    }
}
