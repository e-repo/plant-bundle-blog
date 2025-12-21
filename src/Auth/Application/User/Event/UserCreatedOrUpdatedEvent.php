<?php

declare(strict_types=1);

namespace Auth\Application\User\Event;

use CoreKit\Application\Event\UserCreatedOrUpdatedEventInterface;

final readonly class UserCreatedOrUpdatedEvent implements UserCreatedOrUpdatedEventInterface
{
    public function __construct(
        private string $id,
        private string $firstname,
        private ?string $lastname,
        private string $email,
        private string $status,
        private string $role,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function geLastname(): ?string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
