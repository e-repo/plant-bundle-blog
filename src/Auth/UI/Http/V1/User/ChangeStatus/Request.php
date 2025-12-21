<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\ChangeStatus;

use Auth\Domain\User\Entity\Status;
use CoreKit\UI\Http\Request\RequestPayloadInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\Uuid(message: 'Не валидный идентификатор пользователя.')]
    #[Assert\NotBlank(message: 'Не указан идентификатор пользователя.')]
    #[Assert\Length(min: 36, minMessage: 'Идентификатор пользователя не может быть менее 36 символов.')]
    public string $id;

    #[Assert\NotBlank(message: 'Не указан статус пользователя.')]
    #[Assert\Choice(
        options: [Status::ACTIVE->value, Status::BLOCKED->value],
        message: 'Передан не допустимый статус пользователя.'
    )]
    public string $status;
}
