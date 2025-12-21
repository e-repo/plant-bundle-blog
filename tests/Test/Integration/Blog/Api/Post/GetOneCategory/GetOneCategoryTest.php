<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\GetOneCategory;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use JsonException;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class GetOneCategoryTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;

    private const ENDPOINT_URL = '/api/blog/v1/post/category/%s';

    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                CategoryFixture::class,
            ]);
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetOneCategory(): void
    {
        // arrange
        $loadedCategory = CategoryFixture::allItems()[0];
        $client = $this->createClient();

        // action
        $client->request(
            method: 'GET',
            uri: $this->getUrl($loadedCategory['id']),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $responseData = $response['data'];

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($loadedCategory['name'], $responseData['name']);
        self::assertEquals($loadedCategory['description'], $responseData['description']);
        self::assertNotNull($responseData['createdAt']);
    }

    public function testFailedByInvalidId(): void
    {
        // arrange
        $invalidUserId = '41e08435-0cde-4c40-8b28-8191dfae367b';
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => sprintf("Категория по идентификатору '%s' не найдена", $invalidUserId),
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->request(
            method: 'GET',
            uri: $this->getUrl($invalidUserId),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsUnprocessable();
        self::assertEquals($expectedResponse, $response);
    }

    private function getUrl(string $categoryId): string
    {
        return sprintf(self::ENDPOINT_URL, $categoryId);
    }
}
