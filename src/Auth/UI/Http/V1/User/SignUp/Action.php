<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\SignUp;

use Auth\Application\User\Command\SignUp\Command;
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
    summary: 'Создание пользователя',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(ref: new Model(type: Payload::class))
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Пользователь успешно создан',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        status: 'Пользователь создан успешно.'
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
        path: 'api/auth/v1/user/sign-up',
        name: 'auth_sign-up',
        methods: ['POST']
    )]
    public function __invoke(Payload $payload): JsonResponse
    {
        $command = new Command(
            firstName: $payload->firstName,
            email: $payload->email,
            password: $payload->password,
            registrationSource: $payload->registrationSource
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(
            new ResponseWrapper(
                data: new Response('Пользователь создан успешно.')
            )
        );
    }
}
