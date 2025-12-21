<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\ChangeStatus;

use Auth\Domain\User\Entity\Status;
use Auth\Domain\User\Entity\User;
use Blog\Application\Reader\Listener\UserCreatedOrUpdatedListener;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\Listener\MockUserCreatedOrUpdatedListener;
use Test\Integration\Common\User\UserBuilder;

final class ChangeUserStatusTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const ENDPOINT_URL = '/api/auth/v1/user/%s/status';

    private ReferenceRepository $referenceRepository;

    public function setUp(): void
    {
        parent::setUp();

        // Убираем создание Reader - тестируется отдельно
        $userCreatedOrUpdatedListener = $this->createMock(MockUserCreatedOrUpdatedListener::class);
        $this->container->set(UserCreatedOrUpdatedListener::class, $userCreatedOrUpdatedListener);

        // arrange
        $this->referenceRepository = $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                UserFixture::class,
            ])
            ->getReferenceRepository();
    }

    /**
     * @throws JsonException
     */
    public function testSuccessActivate(): void
    {
        // arrange
        $client = $this->createClient();

        $loadedUser = $this->referenceRepository->getReference(
            name: UserFixture::getReferenceName(3),
            class: User::class
        );

        $expectedResponse = [
            'data' => [
                'status' => 'Статус пользователя успешно изменен.',
            ],
            'meta' => null,
        ];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($loadedUser->getId()->value),
            parameters: [
                'status' => 'active',
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);
        self::assertEquals($loadedUser->getStatus()->value, Status::ACTIVE->value);
    }

    /**
     * @throws JsonException
     */
    public function testSuccessBlocked(): void
    {
        // arrange
        $client = $this->createClient();

        /** @var User $loadedUser */
        $loadedUser = $this->referenceRepository->getReference(
            name: UserFixture::getReferenceName(2),
            class: User::class
        );

        $expectedResponse = [
            'data' => [
                'status' => 'Статус пользователя успешно изменен.',
            ],
            'meta' => null,
        ];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($loadedUser->getId()->value),
            parameters: [
                'status' => 'blocked',
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);
        self::assertEquals($loadedUser->getStatus()->value, Status::BLOCKED->value);
    }

    /**
     * @throws JsonException
     */
    public function testFailedActiveStatus(): void
    {
        // arrange
        $client = $this->createClient();

        $loadedUser = $this->referenceRepository->getReference(
            name: UserFixture::getReferenceName(1),
            class: User::class
        );

        $expectedErrorMessage = 'Невозможно изменить статус пользователя. На почту была отправлена ссылка для подтверждения email, перейдите по ссылке.';

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($loadedUser->getId()->value),
            parameters: [
                'status' => 'active',
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }

    public function testFailedInvalidStatus(): void
    {
        // arrange
        $client = $this->createClient();

        $loadedUser = $this->referenceRepository->getReference(
            name: UserFixture::getReferenceName(1),
            class: User::class
        );

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($loadedUser->getId()->value),
            parameters: [
                'status' => 'invalid_status',
            ]
        );

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws JsonException
     */
    public function testFailedUserNotFound(): void
    {
        // arrange
        $client = $this->createClient();
        $invalidUserId = $this->faker->uuid();

        $expectedErrorMessage = sprintf('Пользователь по идентификатору \'%s\' не найден.', $invalidUserId);

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($invalidUserId),
            parameters: [
                'status' => 'active',
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }

    /**
     * @throws JsonException
     */
    public function testFailedAccessDenied(): void
    {
        // arrange
        $client = $this->createClient();

        /** @var User $loadedUser */
        $loadedUser = $this->referenceRepository->getReference(
            name: UserFixture::getReferenceName(1),
            class: User::class
        );

        $expectedErrorMessage = 'Доступ запрещен.';

        $client->loginUser(
            UserBuilder::createUser()->build()
        );

        // action
        $client->jsonRequest(
            method: 'PATCH',
            uri: $this->makeUrl($loadedUser->getId()->value),
            parameters: [
                'status' => 'blocked',
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }

    private function makeUrl(string $id): string
    {
        return sprintf(self::ENDPOINT_URL, $id);
    }
}
