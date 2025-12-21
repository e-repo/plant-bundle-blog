<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\ConfirmEmail;

use Blog\Application\Reader\Listener\UserCreatedOrUpdatedListener;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Exception;
use JsonException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\Listener\MockUserCreatedOrUpdatedListener;

final class ConfirmEmailTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const ENDPOINT_URL = '/api/auth/v1/user/confirm-email';

    public function setUp(): void
    {
        parent::setUp();

        // arrange
        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                UserFixture::class,
            ]);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testSuccessConfirmEmail(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[0];
        $client = $this->createClient();

        // Убираем создание Reader по событию - тестируется отдельно
        $userCreatedOrUpdatedListener = $this->createMock(MockUserCreatedOrUpdatedListener::class);
        $this->container->set(UserCreatedOrUpdatedListener::class, $userCreatedOrUpdatedListener);

        $expectedResponse = [
            'data' => [
                'status' => 'Email пользователя успешно подтвержден.',
            ],
            'meta' => null,
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'userId' => $loadedUser['id'],
                'token' => $this->getConfirmTokenByUserId($loadedUser['id']),
            ]
        );

        // assert
        $changedUser = $this->getUserById($loadedUser['id']);

        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);

        self::assertEquals('active', $changedUser['status']);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testFailedByInvalidConfirmToken(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[0];
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => 'Передан не верный токен для подтверждения email.',
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'userId' => $loadedUser['id'],
                'token' => Uuid::uuid4()->toString(),
            ]
        );

        // assert
        $savedUser = $this->getUserById($loadedUser['id']);

        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals($expectedResponse, $response);
        self::assertEquals('wait', $savedUser['status']);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testFailedByInvalidUserId(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[0];
        $client = $this->createClient();
        $fakeUserId = Uuid::uuid4()->toString();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => sprintf('Пользователь с идентификатором %s не найден.', $fakeUserId),
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'userId' => $fakeUserId,
                'token' => Uuid::uuid4()->toString(),
            ]
        );

        // assert
        $savedUser = $this->getUserById($loadedUser['id']);
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals($expectedResponse, $response);
        self::assertEquals('wait', $savedUser['status']);
    }

    /**
     * @throws Exception
     */
    private function getUserById(string $id): array
    {
        return $this->queryBuilder
            ->select('*')
            ->from('auth.user', 'u')
            ->where(
                $this->queryBuilder->expr()->eq('u.id', ':id')
            )
            ->setParameter('id', $id)
            ->fetchAssociative();
    }

    /**
     * @throws Exception
     */
    private function getConfirmTokenByUserId(string $userId): ?string
    {
        $user = $this->getUserById($userId);

        return $user['email_confirm_token'] ?? null;
    }
}
