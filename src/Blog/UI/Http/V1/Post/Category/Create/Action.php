<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Category\Create;

use Blog\Application\Post\Category\Command\Create\Command;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\Infra\Security\Role;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Блог: категория поста')]
#[OA\Post(
    summary: 'Создание категории поста',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: Request::class))
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Категория поста успешно создана',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        status: 'Категория поста успешно создана.'
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
        private readonly CommandBusInterface $commandBus,
    ) {}

    #[Route(
        path: '/api/blog/v1/post/category',
        name: 'blog_create-post-category',
        methods: ['POST']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): JsonResponse
    {
        $this->commandBus->dispatch(
            new Command(
                name: $request->name,
                description: $request->description,
            )
        );

        return new JsonResponse(
            new ResponseWrapper(
                data: new Response('Категория поста создана успешно.')
            )
        );
    }
}
