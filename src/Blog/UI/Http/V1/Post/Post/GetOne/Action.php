<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\GetOne;

use Blog\Application\Post\Post\Query\GetOne\Query;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\UI\Http\V1\Post\Post\GetOne\Response\ImageResponse;
use Blog\UI\Http\V1\Post\Post\GetOne\Response\MetadataResponse;
use CoreKit\Application\Bus\QueryBusInterface;
use CoreKit\Infra\OpenApiDateTime;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Блог: пост')]
#[OA\Get(
    description: 'Возвращает детальную информацию о посте',
    summary: 'Получение поста по ID',
    parameters: [
        new OA\Parameter(
            name: 'id',
            description: 'Идентификатор поста',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Пользователь успешно создан',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        id: 1,
                        title: 'Заголовок статьи',
                        shortTitle: 'Короткий заголовок',
                        slug: 'zagolovok-stati',
                        content: 'Содержание статьи в формате текста...',
                        status: 'published',
                        createdAt: new OpenApiDateTime(),
                        image: new ImageResponse(
                            main: 'https://example.com/images/main.jpg',
                            content: []
                        ),
                        commentAvailable: true,
                        categoryName: 'Регуляторы роста',
                        meta: new MetadataResponse(
                            keyword: 'ключевые, слова, через, запятую',
                            description: 'Мета-описание статьи'
                        )
                    )
                )
            )
        ),
        new OA\Response(
            response: 400,
            description: 'Некорректные данные запроса.',
            content: new Model(type: Violation::class),
        ),
        new OA\Response(
            response: 422,
            description: 'Ошибка бизнес-логики.',
            content: new Model(type: Violation::class),
        ),
    ]
)]
final class Action extends AbstractController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {}

    #[Route(
        path: '/api/blog/v1/post/{id}',
        name: 'blog_get-post',
        methods: ['GET']
    )]
    public function __invoke(Request $request): ResponseWrapper
    {
        /** @var PostDto $result */
        $result = $this->queryBus->dispatch(
            new Query($request->id)
        );

        return new ResponseWrapper(
            new Response(
                id: $result->id,
                title: $result->title,
                shortTitle: $result->shortTitle,
                slug: $result->slug,
                content: $result->content->text,
                status: $result->status->value,
                createdAt: $result->createdAt,
                image: new ImageResponse(
                    main: $result->image->url,
                    content: $result->content->images
                ),
                commentAvailable: $result->commentAvailable,
                categoryName: $result->categoryName,
                meta: $result->meta
                    ? new MetadataResponse(
                        keyword: $result->meta->keyword,
                        description: $result->meta->description
                    )
                    : null,
            )
        );
    }
}
