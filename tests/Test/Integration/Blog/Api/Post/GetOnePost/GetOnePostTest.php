<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\GetOnePost;

use CoreKit\Infra\FileStorage\S3StorageClient;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Common\FileStorage\S3StorageMockConfiguratorTrait;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class GetOnePostTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;
    use S3StorageMockConfiguratorTrait;

    private const ENDPOINT_URL = '/api/blog/v1/post/%s';

    public function setUp(): void
    {
        parent::setUp();

        // Мокаем S3StorageClient
        $this->setMockS3StorageClient();

        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                CategoryFixture::class,
                PostFixture::class,
                FileMetadataFixture::class,
            ]);
    }

    /**
     * @throws JsonException
     */
    public function testSuccessGetOnePost(): void
    {
        // arrange
        $loadedPost = PostFixture::allItems()[0];
        $client = $this->createClient();

        // Настраиваем мок S3StorageClient для возврата публичного URL
        $s3ClientMock = $this->container->get(S3StorageClient::class);
        $s3ClientMock->method('publicUrl')
            ->willReturn('https://example.com/images/test-image.jpg');

        // action
        $client->request(
            method: 'GET',
            uri: $this->getUrl($loadedPost['id']),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());
        $responseData = $response['data'];

        // assert
        self::assertResponseIsSuccessful();
        self::assertEquals($loadedPost['title'], $responseData['title']);
        self::assertEquals($loadedPost['shortTitle'], $responseData['shortTitle']);
        self::assertEquals($loadedPost['content'], $responseData['content']);
        self::assertEquals($loadedPost['slug'], $responseData['slug']);
        self::assertEquals($loadedPost['status'], $responseData['status']);
        self::assertEquals($loadedPost['commentAvailable'], $responseData['commentAvailable']);
        self::assertEquals($loadedPost['metaKeyword'], $responseData['meta']['keyword']);
        self::assertEquals($loadedPost['metaDescription'], $responseData['meta']['description']);
        self::assertNotNull($responseData['createdAt']);
    }

    public function testFailedByEmptyId(): void
    {
        // arrange
        $shortId = 'short-id'; // ID короче 36 символов для UUID
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Некорректные данные запроса.',
            'errors' => [
                [
                    'detail' => 'Не валидный идентификатор поста.',
                    'source' => 'id',
                    'data' => [],
                ],
                [
                    'detail' => 'Идентификатор поста не может быть менее 36 символов.',
                    'source' => 'id',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->request(
            method: 'GET',
            uri: $this->getUrl($shortId),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertEquals($expectedResponse, $response);
    }

    public function testFailedByInvalidId(): void
    {
        // arrange
        $invalidPostId = '41e08435-0cde-4c40-8b28-8191dfae367b';
        $client = $this->createClient();

        $expectedResponse = [
            'message' => 'Ошибка бизнес-логики.',
            'errors' => [
                [
                    'detail' => 'Пост не найден.',
                    'source' => '',
                    'data' => [],
                ],
            ],
        ];

        // action
        $client->request(
            method: 'GET',
            uri: $this->getUrl($invalidPostId),
        );

        $response = $this->getDataFromJsonResponse($client->getResponse());

        // assert
        self::assertResponseIsUnprocessable();
        self::assertEquals($expectedResponse, $response);
    }

    private function getUrl(string $postId): string
    {
        return sprintf(self::ENDPOINT_URL, $postId);
    }
}
