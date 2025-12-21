<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\Create\Request;

use CoreKit\Infra\Validator\NotWhitespace\NotWhitespace;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final class PostRequest
{
    #[NotWhitespace]
    #[Assert\NotBlank(message: 'Пустой заголовок поста.')]
    #[Assert\Length(
        min: 4,
        max: 250,
        minMessage: 'Заголовок должен содержать не менее 4 символов.',
        maxMessage: 'Заголовок должен содержать не более 250 символов.'
    )]
    #[OA\Property(example: 'Бонсай')]
    public string $title;

    #[NotWhitespace]
    #[Assert\NotBlank(message: 'Пустой короткий заголовок поста.')]
    #[Assert\Length(
        min: 4,
        max: 100,
        minMessage: 'Короткий заголовок должен содержать не менее 4 символов.',
        maxMessage: 'Короткий Заголовок должен содержать не более 100 символов.'
    )]
    #[OA\Property(example: 'Бонсай')]
    public string $shortTitle;

    #[NotWhitespace]
    #[Assert\NotBlank(message: 'Пустой короткий заголовок поста.')]
    #[Assert\Length(
        min: 40,
        max: 20000,
        minMessage: 'Пост должен содержать не менее 4 символов.',
        maxMessage: 'Пост должен содержать не более 20000 символов.'
    )]
    #[OA\Property(example: 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре')]
    public string $content;

    #[Assert\Uuid(message: 'Передан не валидный UUID')]
    #[Assert\NotBlank(message: 'Невозможно создать пост, т.к. не указана категория поста.')]
    #[OA\Property(example: 'f84c29cf-5f37-47d5-a790-8ca8008bbdf8')]
    public string $categoryUuid;

    #[Assert\Length(
        max: 250,
        maxMessage: 'Ключевые слова поста для seo не могут содержать более 250 символов.'
    )]
    #[OA\Property(example: 'бонсай, дерево в миниатюре')]
    public ?string $metaKeyword = null;

    #[Assert\Length(
        max: 250,
        maxMessage: 'Seo описание поста не может содержать более 250 символов.'
    )]
    #[OA\Property(example: 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре')]
    public ?string $metaDescription = null;
}
