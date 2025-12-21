<?php

declare(strict_types=1);

namespace Service\UI\Http\V1\Menu;

use CoreKit\Application\Bus\QueryBusInterface;
use CoreKit\Infra\Security\Role;
use CoreKit\UI\Http\Response\ResponseWrapper;
use CoreKit\UI\Http\Response\Violation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Service\Application\Menu\Query\GetMenu\Query;
use Service\Application\Menu\Query\GetMenu\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Сервисные URL')]
#[OA\Get(
    summary: 'Получение меню',
    parameters: [
        new OA\Parameter(
            name: 'name',
            description: 'Наименование меню',
            in: 'query',
            schema: new OA\Schema(type: 'string', example: 'service')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Пользователь успешно создан',
            content: new OA\JsonContent(
                ref: new Model(type: ResponseWrapper::class),
                example: new ResponseWrapper(
                    data: [
                        new Response(
                            id: 'service_home',
                            title: 'Главная',
                            icon: 'home-silo-outline',
                        ),
                        new Response(
                            id: 'service_users',
                            title: 'Пользователи',
                            icon: 'account-group-outline',
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
            response: 404,
            description: 'Ошибка бизнес-логики.',
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
        path: '/api/service/v1/menu',
        name: 'service_get-menu',
        methods: ['GET']
    )]
    #[IsGranted(attribute: Role::ADMIN->value)]
    public function __invoke(Request $request): ResponseWrapper
    {
        /** @var Result $result */
        $result = $this->queryBus
            ->dispatch(
                new Query($request->name)
            );

        return new ResponseWrapper(
            data: array_map($this->toMenuResponse(...), $result->menuItems)
        );
    }

    private function toMenuResponse(Result\MenuItemDto $menuItemDto): Response
    {
        return new Response(
            id: $menuItemDto->id,
            title: $menuItemDto->title,
            icon: $menuItemDto->icon,
        );
    }
}
