<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\GetUser;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\User\UserBuilder;

final class GetUserTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;

    private const ENDPOINT_URL = '/api/auth/v1/user/%s';

    public function setUp(): void
    {
        parent::setUp();

        // arrange
        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                UserFixture::class,
            ]);

        $this->mailerListener->reset();
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUser(): void
    {
        // arrange
        $loadedUser = UserFixture::allItems()[1];
        $client = $this->createClient();

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: sprintf(self::ENDPOINT_URL, $loadedUser['id']),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $responseData = $response['data'];

        // assert
        self::assertResponseIsSuccessful();
        self::assertNull($response['meta']);
        self::assertEquals($loadedUser['id'], $responseData['id']);
        self::assertEquals($loadedUser['firstName'], $responseData['firstName']);
        self::assertEquals($loadedUser['lastName'], $responseData['lastName']);
        self::assertEquals($loadedUser['email'], $responseData['email']);
        self::assertEquals('ROLE_USER', $responseData['role']);
        self::assertEquals($loadedUser['status'], $responseData['status']);
        self::assertNotNull($responseData['createdAt']);
    }

    /**
     * @throws JsonException
     */
    public function testFailedByInvalidId(): void
    {
        // arrange
        $invalidUserId = '41e08435-0cde-4c40-8b28-8191dfae367b';
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => sprintf("Пользователь по идентификатору '%s' не найден", $invalidUserId),
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: sprintf(self::ENDPOINT_URL, $invalidUserId),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsUnprocessable();
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws JsonException
     */
    public function testFailedAccessDenied(): void
    {
        // arrange
        $invalidUserId = '41e08435-0cde-4c40-8b28-8191dfae367b';
        $client = $this->createClient();

        $expectedErrorMessage = 'Доступ запрещен.';

        $client->loginUser(
            UserBuilder::createUser()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: sprintf(self::ENDPOINT_URL, $invalidUserId),
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }
}
