<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\GetList;

use Auth\Application\User\Query\GetList\Query;
use Auth\Domain\User\Dto\UserProfileDto;
use Auth\Domain\User\Dto\UsersByListFilterDto;
use CoreKit\Application\Bus\QueryBusInterface;
use CoreKit\Infra\OpenApiDateTime;
use CoreKit\Infra\Security\Role;
use CoreKit\UI\Http\Response\ResponseMeta;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Регистрация')]
#[OA\Get(
    summary: 'Получение списка пользователей',
    parameters: [
        new OA\Parameter(ref: '#/components/parameters/limitParam'),
        new OA\Parameter(ref: '#/components/parameters/offsetParam'),
        new OA\Parameter(
            name: 'firstName',
            description: 'Имя пользователя',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'Александр')
        ),
        new OA\Parameter(
            name: 'lastName',
            description: 'Фамилия пользователя',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'Иванович')
        ),
        new OA\Parameter(
            name: 'email',
            description: 'E-mail',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'alex@mail.ru')
        ),
        new OA\Parameter(
            name: 'role',
            description: 'Роль пользователя',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'ROLE_USER')
        ),
        new OA\Parameter(
            name: 'status',
            description: 'Статус пользователя',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'active')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Список пользователей',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: [
                        new Response(
                            id: '789739fd-6c6f-4cb7-8eb8-3a548a1239b3',
                            firstName: 'Алексей',
                            lastName: 'Иванов',
                            email: 'test@test_1.ru',
                            role: 'ROLE_USER',
                            status: 'active',
                            createdAt: new OpenApiDateTime()
                        ),
                        new Response(
                            id: '789739fd-6c6f-4cb7-8eb8-3a548a1239b3',
                            firstName: 'Иван',
                            lastName: 'Иванов',
                            email: 'test@test_2.ru',
                            role: 'ROLE_USER',
                            status: 'wait',
                            createdAt: new OpenApiDateTime()
                        ),
                    ]
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
        path: 'api/auth/v1/users',
        name: 'auth_get-user-list',
        methods: ['GET']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): ResponseWrapper
    {
        /** @var UsersByListFilterDto $result */
        $result = $this->queryBus->dispatch(
            new Query(
                offset: $request->offset,
                limit: $request->limit,
                firstName: $request->firstName,
                lastName: $request->lastName,
                email: $request->email,
                role: $request->role,
                status: $request->status
            )
        );

        return new ResponseWrapper(
            data: array_map($this->makeUserResponse(...), $result->userList),
            meta: new ResponseMeta(
                offset: $request->offset,
                limit: $request->limit,
                total: $result->total
            )
        );
    }

    private function makeUserResponse(UserProfileDto $userDto): Response
    {
        return new Response(
            id: $userDto->id,
            firstName: $userDto->firstName,
            lastName: $userDto->lastName,
            email: $userDto->email,
            role: $userDto->role,
            status: $userDto->status,
            createdAt: $userDto->createdAt
        );
    }
}
