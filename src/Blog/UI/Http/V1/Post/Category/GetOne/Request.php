<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Category\GetOne;

use CoreKit\UI\Http\Request\RequestPayloadInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\Uuid(message: 'Не валидный идентификатор категории.')]
    #[Assert\NotBlank(message: 'Не указан идентификатор категории.')]
    #[Assert\Length(min: 36, minMessage: 'Идентификатор категории не может быть менее 36 символов.')]
    public string $id;
}
