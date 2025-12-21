<?php

declare(strict_types=1);

namespace Test\Integration\Auth\Api\User\GetUserList;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\User\UserBuilder;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class GetUserListTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;

    private const ENDPOINT_URL = '/api/auth/v1/users';

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
    public function testSuccessGetUserList(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedFields = [
            'id',
            'firstName',
            'lastName',
            'email',
            'role',
            'status',
            'createdAt',
        ];

        $expectedUserIds = array_map(
            static fn (array $user) => $user['id'],
            UserFixture::allItems()
        );

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($offset, $response['meta']['offset']);
        self::assertEquals($limit, $response['meta']['limit']);

        self::assertCount($response['meta']['total'], UserFixture::allItems());

        foreach ($response['data'] as $userResponse) {
            self::assertEqualsCanonicalizing($expectedFields, array_keys($userResponse));

            self::assertContains($userResponse['id'], $expectedUserIds);
        }
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUserByFirstNameFilter(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedUserIds = [
            UserFixture::allItems()[0]['id'],
            UserFixture::allItems()[4]['id'],
        ];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
                'firstName' => 'TestFirstName_1',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertCount($response['meta']['total'], $expectedUserIds);
        self::assertCount(count($expectedUserIds), $response['data']);

        foreach ($response['data'] as $userResponse) {
            self::assertContains($userResponse['id'], $expectedUserIds);
        }
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUserByLastNameFilter(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedUserIds = [
            UserFixture::allItems()[0]['id'],
            UserFixture::allItems()[4]['id'],
        ];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
                'lastName' => 'TestLastName_1',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertCount($response['meta']['total'], $expectedUserIds);
        self::assertCount(count($expectedUserIds), $response['data']);

        foreach ($response['data'] as $userResponse) {
            self::assertContains($userResponse['id'], $expectedUserIds);
        }
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUserByEmailFilter(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedUserId = UserFixture::allItems()[1]['id'];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
                'email' => 'test_2@test.ru',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals(1, $response['meta']['total']);
        self::assertCount(1, $response['data']);

        foreach ($response['data'] as $userResponse) {
            self::assertEquals($expectedUserId, $userResponse['id']);
        }
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUserByRoleFilter(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
                'role' => 'ROLE_ADMIN',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals(0, $response['meta']['total']);
        self::assertCount(0, $response['data']);
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetUserByStatusFilter(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedUserId = UserFixture::allItems()[2]['id'];

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
                'status' => 'blocked',
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals(1, $response['meta']['total']);
        self::assertCount(1, $response['data']);

        foreach ($response['data'] as $userResponse) {
            self::assertEquals($expectedUserId, $userResponse['id']);
        }
    }

    /**
     * @throws JsonException
     */
    public function testFailedWithoutOffset(): void
    {
        // arrange
        $limit = 10;
        $client = $this->createClient();

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'limit' => $limit,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $errors = $response['errors'];

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertEquals('Не заполнено поле offset', $errors[0]['detail']);
    }

    /**
     * @throws JsonException
     */
    public function testFailedWithoutLimit(): void
    {
        // arrange
        $offset = 0;
        $client = $this->createClient();

        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $errors = $response['errors'];

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertEquals('Не заполнено поле limit', $errors[0]['detail']);
    }

    /**
     * @throws JsonException
     */
    public function testFailedAccessDenied(): void
    {
        // arrange
        $offset = 0;
        $limit = 10;
        $client = $this->createClient();

        $expectedErrorMessage = 'Доступ запрещен.';

        $client->loginUser(
            UserBuilder::createUser()->build()
        );

        // action
        $client->request(
            method: 'GET',
            uri: self::ENDPOINT_URL,
            parameters: [
                'offset' => $offset,
                'limit' => $limit,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $error = reset($response['errors']);
        self::assertEquals($expectedErrorMessage, $error['detail']);
    }
}
