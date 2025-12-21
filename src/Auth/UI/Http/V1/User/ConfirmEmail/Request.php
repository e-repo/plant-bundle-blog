<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\ConfirmEmail;

use CoreKit\UI\Http\Request\RequestPayloadInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\NotBlank(message: 'Идентификатор пользователя не может быть пустым.')]
    #[Assert\Length(min: 36, minMessage: 'Идентификатор пользователя не может быть менее 36 символов.')]
    #[Assert\Uuid(message: 'Передан не валидный идентификатор пользователя.')]
    #[OA\Property(example: '3d590297-41f8-4696-8c2f-3c0dca84fd9c')]
    public string $userId;

    #[Assert\NotBlank(message: 'Токен подтверждения emial не может быть пустым.')]
    #[Assert\Length(min: 36, minMessage: 'Токен подтверждения emial не может быть менее 36 символов.')]
    #[Assert\Uuid(message: 'Передан не валидный токен подтверждения email.')]
    #[OA\Property(example: '3d590297-41f8-4696-8c2f-3c0dca84fd9c')]
    public string $token;
}
