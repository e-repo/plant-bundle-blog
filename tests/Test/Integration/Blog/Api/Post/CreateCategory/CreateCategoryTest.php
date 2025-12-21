<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\CreateCategory;

use Blog\Domain\Post\Entity\Category;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\User\UserBuilder;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class CreateCategoryTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;

    private const ENDPOINT_URL = '/api/blog/v1/post/category';

    private ObjectRepository $categoryRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                CategoryFixture::class,
            ]);

        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
    }

    public function testSuccessCreateCategory(): void
    {
        // arrange
        $name = 'Тестовое имя';
        $description = 'Тестовое описание';

        $expectedResponse = [
            'data' => [
                'status' => 'Категория поста создана успешно.',
            ],
            'meta' => null,
        ];

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'name' => $name,
                'description' => $description,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($expectedResponse, $response);
    }

    public function testFailedBySameName(): void
    {
        // arrange
        $name = 'Регуляторы роста';
        $description = 'Тестовое описание';

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'name' => $name,
                'description' => $description,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals('Ошибка бизнес-логики.', $response['message']);
        self::assertEquals(
            'Категория поста с данными наименованием уже существует.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedPublicUser(): void
    {
        // arrange
        $name = 'Тестовое имя';
        $description = 'Тестовое описание';

        $client = $this->createClient();

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'name' => $name,
                'description' => $description,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertEquals('Ошибка бизнес-логики.', $response['message']);
        self::assertEquals(
            'Доступ запрещен.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedNotAdmin(): void
    {
        // arrange
        $name = 'Тестовое имя';
        $description = 'Тестовое описание';

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createUser()->build()
        );

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'name' => $name,
                'description' => $description,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertEquals('Ошибка бизнес-логики.', $response['message']);
        self::assertEquals(
            'Доступ запрещен.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedByWhitespace(): void
    {
        // arrange
        $name = '   ';
        $description = '   ';

        $expectedResponse = [
            'message' => 'Некорректные данные запроса.',
            'errors' => [
                [
                    'detail' => 'Поле \'name\' не может состоять только из пробелов.',
                    'source' => 'name',
                    'data' => [],
                ],
                [
                    'detail' => 'Поле \'description\' не может состоять только из пробелов.',
                    'source' => 'description',
                    'data' => [],
                ],
            ],
        ];

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        // action
        $client->jsonRequest(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'name' => $name,
                'description' => $description,
            ]
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertEquals($expectedResponse, $response);
    }
}
