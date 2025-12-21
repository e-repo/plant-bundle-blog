<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\ChangeStatus;

use Auth\Application\User\Command\ChangeStatus\Command;
use Auth\UI\Http\V1\User\ConfirmEmail\Response;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\Application\Security\AuthenticationInterface;
use CoreKit\Infra\Security\Role;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Регистрация')]
#[OA\Patch(
    summary: 'Изменение статуса пользователя',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            ref: new Model(type: Request::class),
            example: [
                'status' => 'active',
            ]
        )
    ),
    parameters: [
        new OA\Parameter(
            name: 'id',
            description: 'Идентификатор пользователя',
            in: 'path'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Статус пользователя успешно изменен.',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        status: 'Статус пользователя успешно изменен.'
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
        private readonly AuthenticationInterface $authenticationService,
    ) {}

    #[Route(
        path: 'api/auth/v1/user/{id}/status',
        name: 'auth_change-status',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['PATCH']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): ResponseWrapper
    {
        $user = $this->authenticationService->getUser();

        $this->commandBus->dispatch(
            new Command(
                id: $request->id,
                status: $request->status,
                changedBy: $user?->email,
            )
        );

        return new ResponseWrapper(
            data: new Response('Статус пользователя успешно изменен.')
        );
    }
}
