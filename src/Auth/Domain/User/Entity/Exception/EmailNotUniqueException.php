<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity\Exception;

use DomainException;

final class EmailNotUniqueException extends DomainException
{
    private const DEFAULT_MESSAGE = 'Пользователь с данным email уже существует.';

    public function __construct(
        private readonly string $email,
        ?string $message = null
    ) {
        parent::__construct($message ?? self::DEFAULT_MESSAGE);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
