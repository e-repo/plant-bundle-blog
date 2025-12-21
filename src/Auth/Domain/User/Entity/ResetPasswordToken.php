<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Embeddable]
final class ResetPasswordToken
{
    #[ORM\Column(name: 'reset_password_token', length: 50, nullable: true, options: [
        'comment' => 'Токен сброса пароля',
    ])]
    private ?string $token;

    #[ORM\Column(
        name: 'password_token_expires',
        type: Types::DATETIMETZ_IMMUTABLE,
        nullable: true,
        options: [
            'comment' => 'Дата действия токена сброса пароля',
        ]
    )]
    private ?DateTimeImmutable $expires;

    public function __construct(
        ?string $resetPasswordToken = null,
        ?DateTimeImmutable $passwordTokenExpires = null,
    ) {
        $this->token = $resetPasswordToken;
        $this->expires = $passwordTokenExpires;
    }

    public function isExpired(DateTimeImmutable $date): bool
    {
        if (null === $this->expires) {
            throw new DomainException('Ошибка определения срок действия токена');
        }

        return $this->expires <= $date;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpires(): ?DateTimeImmutable
    {
        return $this->expires;
    }
}
