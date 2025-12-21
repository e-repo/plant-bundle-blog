<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity\Event;

use CoreKit\Domain\Event\DomainEventInterface;

final readonly class UserStatusChangedEvent implements DomainEventInterface
{
    public function __construct(
        private string $id,
        private string $firstname,
        private string $lastname,
        private string $email,
        private string $status,
        private string $role,
        private ?string $changedBy,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): ?string
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

    public function getChangedBy(): string
    {
        return $this->changedBy;
    }
}
