<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity;

use Auth\Domain\User\Entity\Event\UserCreatedEvent;
use Auth\Domain\User\Entity\Event\UserPasswordResetEvent;
use Auth\Domain\User\Entity\Event\UserStatusChangedEvent;
use Auth\Domain\User\Entity\Exception\EmailNotUniqueException;
use Auth\Domain\User\Entity\Specification\UniqueEmailSpecification;
use Auth\Domain\User\Service\PasswordHasher\Hasher;
use Auth\Domain\User\Service\PasswordHasher\PasswordHashedUserInterface;
use CoreKit\Domain\Entity\Email;
use CoreKit\Domain\Entity\EventRecordTrait;
use CoreKit\Domain\Entity\HasEventsInterface;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(schema: 'auth')]
class User implements PasswordHashedUserInterface, HasEventsInterface
{
    use EventRecordTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', options: [
        'comment' => 'Код пользователя',
    ])]
    private Id $id;

    #[ORM\Embedded(NameVo::class)]
    private NameVo $name;

    #[ORM\Column(type: 'email', length: 100, unique: true)]
    private Email $email;

    #[ORM\Column(length: 50, nullable: true, options: [
        'comment' => 'Токен для подтверждения email',
    ])]
    private ?string $emailConfirmToken = null;

    #[ORM\Column(length: 50, enumType: Status::class, options: [
        'comment' => 'Статус пользователя',
    ])]
    private Status $status;

    #[ORM\Column(length: 100, enumType: Role::class, options: [
        'comment' => 'Роль пользователя',
    ])]
    private Role $role;

    #[ORM\Column(length: 255, options: [
        'comment' => 'Хэш пароля',
    ])]
    private string $passwordHash;

    #[ORM\Column(
        length: 100,
        nullable: true,
        enumType: RegistrationSource::class,
        options: [
            'comment' => 'Система-источник регистрации пользователя',
        ]
    )
    ]
    private RegistrationSource $registrationSource;

    #[ORM\Embedded(class: ResetPasswordToken::class, columnPrefix: false)]
    private ResetPasswordToken $resetPasswordToken;

    #[ORM\Column(
        type: 'email',
        length: 100,
        nullable: true,
        options: [
            'comment' => 'Новый email (при смене)',
        ]
    )]
    private ?Email $newEmail = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, options: [
        'comment' => 'Дата создания пользователя',
    ])]
    private DateTimeImmutable $createdAt;

    // @TODO рассмотреть возможность вынести основные поля пользователя в VO
    public function __construct(
        Id $id,
        NameVo $name,
        Email $email,
        string $password,
        string $registrationSource,
        UniqueEmailSpecification $uniqueEmailSpecification,
        Hasher $hasher,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $password;
        $this->registrationSource = RegistrationSource::tryFrom($registrationSource);

        $this->status = Status::WAIT;
        $this->role = Role::USER;
        $this->resetPasswordToken = new ResetPasswordToken();
        $this->createdAt = new DateTimeImmutable();

        $this->setEmailConfirmToken();

        if (! $uniqueEmailSpecification->isSatisfiedBy($this)) {
            throw new EmailNotUniqueException($email->value);
        }

        $hasher->hash($this);
        $this->record($this->makeUserCreatedEvent());
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): NameVo
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    public function getRegistrationSource(): RegistrationSource
    {
        return $this->registrationSource;
    }

    public function getEmailConfirmToken(): string
    {
        return $this->emailConfirmToken;
    }

    public function changePlainPassword(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function confirmUserEmail(string $emailConfirmToken): void
    {
        if ($emailConfirmToken !== $this->emailConfirmToken) {
            throw new DomainException('Передан не верный токен для подтверждения email.');
        }

        $this->changeStatus(Status::ACTIVE, $this->email->value);
    }

    public function changeStatus(Status $status, ?string $changedBy): void
    {
        $this->status = $status;

        match ($status) {
            Status::WAIT => $this->setEmailConfirmToken(),
            default => $this->resetEmailConfirmToken(),
        };

        $this->record($this->makeUserStatusChangedEvent($changedBy));
    }

    public function changeRole(Role $role): void
    {
        $this->role = $role;
    }

    public function confirmResetPassword(
        string $token,
        string $password,
        Hasher $hasher,
    ): void {
        if ($token !== $this->resetPasswordToken->getToken()) {
            throw new DomainException('Передан не верный токен для сброса пароля.');
        }

        if (! $this->isActive()) {
            throw new DomainException('Невозможно сбросить пароль т.к пользователь не является активным');
        }

        $currentDate = new DateTimeImmutable();

        if ($this->resetPasswordToken->isExpired($currentDate)) {
            throw new DomainException('Токен для сброса пароля уже истек. Действует в течении суток');
        }

        $this->resetPasswordToken = new ResetPasswordToken();

        $this->passwordHash = $password;
        $hasher->hash($this);
    }

    public function requestResetPassword(string $registrationSource): void
    {
        if (! $this->isActive()) {
            throw new DomainException('Невозможно сбросить пароль т.к пользователь не является активным');
        }

        $currentDate = new DateTimeImmutable();

        if (
            null !== $this->resetPasswordToken->getExpires() &&
            ! $this->resetPasswordToken->isExpired($currentDate)
        ) {
            throw new DomainException('Запрос на сброс пароля уже был отправлен. Действует в течении суток');
        }

        $this->resetPasswordToken = new ResetPasswordToken(
            resetPasswordToken: Uuid::uuid4()->toString(),
            passwordTokenExpires: $currentDate->modify('+1 day')
        );

        $this->record(
            new UserPasswordResetEvent(
                email: $this->email->value,
                resetPasswordToken: $this->resetPasswordToken->getToken(),
                passwordTokenExpires: $this->resetPasswordToken->getExpires(),
                registrationSource: $registrationSource
            )
        );
    }

    private function makeUserCreatedEvent(): UserCreatedEvent
    {
        return new UserCreatedEvent(
            id: $this->id->value,
            firstname: $this->name->first,
            lastname: $this->name->last,
            email: $this->email->value,
            emailConfirmToken: $this->emailConfirmToken,
            status: $this->status->value,
            role: $this->role->value,
            registrationSource: $this->registrationSource->value,
            createdAt: $this->createdAt
        );
    }

    private function makeUserStatusChangedEvent(?string $changedBy): UserStatusChangedEvent
    {
        return new UserStatusChangedEvent(
            id: $this->id->value,
            firstname: $this->name->first,
            lastname: $this->name->last,
            email: $this->email->value,
            status: $this->status->value,
            role: $this->role->value,
            changedBy: $changedBy,
        );
    }

    private function isActive(): bool
    {
        return Status::ACTIVE === $this->status;
    }

    private function resetEmailConfirmToken(): void
    {
        $this->emailConfirmToken = null;
    }

    private function setEmailConfirmToken(): void
    {
        $this->emailConfirmToken = Uuid::uuid4()->toString();
    }
}
