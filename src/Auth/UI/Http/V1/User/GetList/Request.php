<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\GetList;

use Auth\Domain\User\Entity\Role;
use Auth\Domain\User\Entity\Status;
use CoreKit\UI\Http\Request\RequestPayloadInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\NotNull(message: 'Не заполнено поле offset')]
    #[Assert\PositiveOrZero(message: 'offset должен быть положительным либо равным нулю')]
    public int $offset;

    #[Assert\NotNull(message: 'Не заполнено поле limit')]
    #[Assert\Positive(message: 'limit не должен быть отрицательным')]
    #[Assert\LessThanOrEqual(value: 100, message: 'limit не может превышать значение 100')]
    public int $limit;

    #[Assert\Length(
        min: 2,
        max: 200,
        minMessage: 'Имя не может содержать менее 2 символов.',
        maxMessage: 'Имя не может содержать более 200 символов.'
    )]
    public ?string $firstName = null;

    #[Assert\Length(
        min: 2,
        max: 200,
        minMessage: 'Фамилия не может содержать менее 2 символов.',
        maxMessage: 'Фамилия не может содержать более 200 символов.'
    )]
    public ?string $lastName = null;

    #[Assert\Length(
        max: 100,
        maxMessage: 'Email не может содержать более 100 символов.'
    )]
    #[Assert\Email(message: 'Значение не соответствует формату email.')]
    public ?string $email = null;

    #[Assert\Choice(callback: [Role::class, 'values'])]
    public ?string $role = null;

    #[Assert\Choice(callback: [Status::class, 'values'])]
    public ?string $status = null;
}
