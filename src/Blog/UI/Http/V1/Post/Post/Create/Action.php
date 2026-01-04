<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Post\Create;

use Blog\Application\Post\Post\Command\Create\Command;
use Blog\UI\Http\V1\Post\Category\Create\Response;
use Blog\UI\Http\V1\Post\Post\Create\Request\PostRequest;
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

#[OA\Tag(name: 'Блог: пост')]
#[OA\Post(
    summary: 'Создание поста',
    requestBody: new OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['file', 'payload'],
                properties: [
                    new OA\Property(
                        property: 'file',
                        description: 'Изображение поста',
                        type: 'file',
                    ),
                    new OA\Property(
                        property: 'payload',
                        ref: new Model(type: PostRequest::class),
                        description: 'Данные по посту'
                    ),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Пост создан успешно.',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        status: 'Пост создан успешно.'
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
        path: '/api/blog/v1/post',
        name: 'blog_create-post',
        methods: ['POST']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): JsonResponse
    {
        $this->commandBus->dispatch(
            new Command(
                image: new Command\ImageCommand(
                    file: $request->file,
                    extension: $request->file->guessExtension(),
                    originalFileName: $request->file->getClientOriginalName(),
                ),
                title: $request->payload->title,
                shortTitle: $request->payload->shortTitle,
                content: $request->payload->content,
                categoryId: $request->payload->categoryUuid,
                meta: new Command\MetaCommand(
                    keyword: $request->payload->metaKeyword,
                    description: $request->payload->metaDescription,
                )
            )
        );

        return new JsonResponse(
            new ResponseWrapper(
                data: new Response('Пост создан успешно.')
            )
        );
    }
}
