<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\ConfirmEmail;

use Auth\Application\User\Command\ConfirmEmail\Command;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Регистрация')]
#[OA\Post(
    summary: 'Подтвержение email пользователя',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: Request::class))
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Email пользователя успешно подтвержден',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        status: 'Email пользователя успешно подтвержден.'
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
        path: 'api/auth/v1/user/confirm-email',
        name: 'auth_confirm-email',
        methods: ['POST']
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $this->commandBus->dispatch(
            new Command(
                userId: $request->userId,
                token: $request->token,
            )
        );

        return new JsonResponse(
            new ResponseWrapper(
                data: new Response('Email пользователя успешно подтвержден.')
            )
        );
    }
}
