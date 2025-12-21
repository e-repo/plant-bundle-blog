<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\GetOne;

use CoreKit\UI\Http\Request\RequestPayloadInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\Uuid(message: 'Не валидный идентификатор поста.')]
    #[Assert\NotBlank(message: 'Не указан идентификатор поста.')]
    #[Assert\Length(min: 36, minMessage: 'Идентификатор поста не может быть менее 36 символов.')]
    public string $id;
}
