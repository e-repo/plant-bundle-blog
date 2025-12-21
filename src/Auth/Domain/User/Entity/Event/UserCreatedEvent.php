<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity\Event;

use CoreKit\Domain\Event\DomainEventInterface;
use DateTimeImmutable;

final readonly class UserCreatedEvent implements DomainEventInterface
{
    public function __construct(
        private string $id,
        private string $firstname,
        private ?string $lastname,
        private string $email,
        private ?string $emailConfirmToken,
        private string $status,
        private string $role,
        private string $registrationSource,
        private DateTimeImmutable $createdAt,
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

    public function getEmailConfirmToken(): ?string
    {
        return $this->emailConfirmToken;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getRegistrationSource(): string
    {
        return $this->registrationSource;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
