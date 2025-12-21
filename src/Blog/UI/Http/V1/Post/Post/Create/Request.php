<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\Create;

use Blog\UI\Http\V1\Post\Post\Create\Request\PostRequest;
use CoreKit\UI\Http\Request\RequestPayloadInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class Request implements RequestPayloadInterface
{
    #[Assert\NotBlank(message: 'Основное изображение поста не передано.')]
    #[Assert\File(
        maxSize: '10M',
        mimeTypes: [
            'image/jpeg',
            'image/pjpeg',
            'image/png',
        ],
        maxSizeMessage: 'Размер файла превышает установленный лимит в 10Мб.',
        mimeTypesMessage: 'Недопустимый тип файла. Ожидается jpeg или png.',
    )]
    public UploadedFile $file;

    #[Assert\Valid]
    #[Assert\NotBlank(message: 'Данные для создания поста не переданы.')]
    public PostRequest $payload;
}
