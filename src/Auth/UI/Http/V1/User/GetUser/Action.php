<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\GetUser;

use Auth\Application\User\Query\GetUser\Query;
use Auth\Domain\User\Dto\UserProfileDto;
use CoreKit\Application\Bus\QueryBusInterface;
use CoreKit\Infra\OpenApiDateTime;
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
#[OA\Get(
    summary: 'Получения пользователя',
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
            description: 'Пользователь успешно создан',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: new Response(
                        id: '789739fd-6c6f-4cb7-8eb8-3a548a1239b3',
                        firstName: 'Алексей',
                        lastName: 'Иванов',
                        email: 'test@test.ru',
                        role: 'ROLE_USER',
                        status: 'active',
                        registrationSource: 'admin_panel',
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
        path: 'api/auth/v1/user/{id}',
        name: 'auth_get-user',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['GET']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): ResponseWrapper
    {
        /** @var UserProfileDto $result */
        $result = $this->queryBus->dispatch(
            new Query($request->id)
        );

        return new ResponseWrapper($this->makeResponse($result));
    }

    private function makeResponse(UserProfileDto $result): Response
    {
        return new Response(
            id: $result->id,
            firstName: $result->firstName,
            lastName: $result->lastName,
            email: $result->email,
            role: $result->role,
            status: $result->status,
            registrationSource: $result->registrationSource,
            createdAt: $result->createdAt,
        );
    }
}
