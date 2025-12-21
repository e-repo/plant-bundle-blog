<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Category\GetOne;

use Blog\Application\Post\Category\Query\GetOne\Query;
use Blog\Domain\Post\Entity\Dto\CategoryDto;
use CoreKit\Application\Bus\QueryBusInterface;
use CoreKit\Infra\OpenApiDateTime;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Блог: категория поста')]
#[OA\Get(
    summary: 'Получение категории по идентификатору.',
    parameters: [
        new OA\Parameter(
            name: 'id',
            description: 'Идентификатор категории',
            in: 'path'
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
                        name: 'Регуляторы роста',
                        description: 'Категория регуляторы роста содержит статьи на тему...',
                        createdAt: new OpenApiDateTime()
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
        path: '/api/blog/v1/post/category/{id}',
        name: 'blog_get-post-category',
        methods: ['GET']
    )]
    public function __invoke(Request $request): ResponseWrapper
    {
        /** @var CategoryDto $result */
        $result = $this->queryBus->dispatch(
            new Query($request->id)
        );

        return new ResponseWrapper(
            new Response(
                name: $result->name,
                description: $result->description,
                createdAt: $result->createdAt,
            )
        );
    }
}
