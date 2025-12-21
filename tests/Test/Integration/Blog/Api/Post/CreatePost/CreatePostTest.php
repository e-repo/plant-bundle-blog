<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\CreatePost;

use Blog\Domain\Post\Entity\Event\MainImageAddedEvent;
use Blog\Domain\Post\Entity\Post;
use Blog\Domain\Post\Entity\PostImage;
use CoreKit\Domain\Entity\FileMetadata;
use CoreKit\Infra\FileStorage\S3StorageClient;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Test\Common\DataFromJsonResponseTrait;
use Test\Common\FunctionalTestCase;
use Test\Integration\Blog\Api\Post\GetOneCategory\CategoryFixture;
use Test\Integration\Common\FileStorage\S3StorageMockConfiguratorTrait;
use Test\Integration\Common\User\UserBuilder;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class CreatePostTest extends FunctionalTestCase
{
    use DataFromJsonResponseTrait;
    use InteractsWithMessenger;
    use S3StorageMockConfiguratorTrait;

    private const ENDPOINT_URL = '/api/blog/v1/post';

    private ObjectRepository $postRepository;

    private ObjectRepository $postImageRepository;

    private ObjectRepository $fileMetadataRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->databaseTool
            ->withPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE)
            ->loadFixtures([
                CategoryFixture::class,
                PostFixture::class,
            ]);

        $this->setMockS3StorageClient();

        $this->postRepository = $this->entityManager->getRepository(Post::class);
        $this->postImageRepository = $this->entityManager->getRepository(PostImage::class);
        $this->fileMetadataRepository = $this->entityManager->getRepository(FileMetadata::class);
    }

    public function testSuccessCreatePost(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        $downloadFilePath = __DIR__ . '/Data/img_download_temp.png';

        copy(__DIR__ . '/Data/img.png', $uploadFilePath);
        copy(__DIR__ . '/Data/img.png', $downloadFilePath);

        $file = fopen($downloadFilePath, 'rb');

        /** @var MockObject $s3ClientStub */
        $s3ClientStub = $this->container->get(S3StorageClient::class);
        $s3ClientStub
            ->method('download')
            ->willReturn($file);

        $client = $this->createClient();
        $eventBus = $this->bus('event.bus');
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Бонсай',
            'shortTitle' => 'Бонсай',
            'content' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
            'categoryUuid' => '28912aa1-96ee-4631-8e34-14cd2f019e53',
            'metaKeyword' => 'бонсай, дерево в миниатюре',
            'metaDescription' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        /** @var Post $post */
        $post = $this->postRepository->findOneBy([
            'shortTitle' => $expectedPost['shortTitle'],
        ]);

        $fileKey = $post
            ->getImages()
            ->first()
            ->getFileKey();

        $fileMetadata = $this->fileMetadataRepository->find($fileKey);

        $eventBus->dispatched()->assertContains(MainImageAddedEvent::class);

        self::assertResponseIsSuccessful();
        self::assertEquals($post->getTitle(), $expectedPost['title']);
        self::assertEquals($post->getShortTitle(), $expectedPost['shortTitle']);
        self::assertEquals($post->getContent(), $expectedPost['content']);
        self::assertEquals($post->getCategory()->getId()->value, $expectedPost['categoryUuid']);
        self::assertEquals($post->getMeta()->keyword, $expectedPost['metaKeyword']);
        self::assertEquals($post->getMeta()->description, $expectedPost['metaDescription']);
        self::assertEquals($fileKey->value, $fileMetadata->getKey());

        if (file_exists($downloadFilePath)) {
            unlink($downloadFilePath);
        }
    }

    public function testFailedByCategoryNotFound(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        copy(__DIR__ . '/Data/img.png', $uploadFilePath);

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Бонсай',
            'shortTitle' => 'Бонсай',
            'content' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
            'categoryUuid' => '762169ba-8c49-4a7d-87cf-703d263e98ae',
            'metaKeyword' => 'бонсай, дерево в миниатюре',
            'metaDescription' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals(
            'Категория не найдена.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedByNotAdmin(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        copy(__DIR__ . '/Data/img.png', $uploadFilePath);

        $client = $this->createClient();

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Бонсай',
            'shortTitle' => 'Бонсай',
            'content' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
            'categoryUuid' => '762169ba-8c49-4a7d-87cf-703d263e98ae',
            'metaKeyword' => 'бонсай, дерево в миниатюре',
            'metaDescription' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertEquals(
            'Доступ запрещен.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedUploadFile(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        copy(__DIR__ . '/Data/img.png', $uploadFilePath);

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        $s3ClientStub = $this->container->get(S3StorageClient::class);
        $s3ClientStub
            ->method('upload')
            ->willReturnCallback(
                static fn () => throw new RuntimeException('Ошибка загрузки файла.')
            );

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Бонсай',
            'shortTitle' => 'Бонсай',
            'content' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
            'categoryUuid' => '28912aa1-96ee-4631-8e34-14cd2f019e53',
            'metaKeyword' => 'бонсай, дерево в миниатюре',
            'metaDescription' => 'Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals(
            'Не удалось загрузить изображение в хранилище. Попробуйте позднее или свяжитесь с администратором.',
            $response['errors'][0]['detail']
        );
    }

    public function testFailedByTitleAlreadyExists(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        copy(__DIR__ . '/Data/img.png', $uploadFilePath);

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Суккуленты',
            'shortTitle' => 'Суккуленты 1',
            'content' => 'Суккуленты - растения, приспособленные для хранения воды в своих листьях или стеблях',
            'categoryUuid' => '28912aa1-96ee-4631-8e34-14cd2f019e53',
            'metaKeyword' => 'суккуленты, уход за суккулентами',
            'metaDescription' => 'Суккуленты - идеальные растения для любителей минимального ухода',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        $postCount = $this->postRepository->count([
            'title' => $expectedPost['title'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals(
            'Данный заголовок статьи уже существует.',
            $response['errors'][0]['detail']
        );
        self::assertEquals(1, $postCount);
    }

    public function testFailedByShortTitleAlreadyExists(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img_upload_temp.png';
        copy(__DIR__ . '/Data/img.png', $uploadFilePath);

        $client = $this->createClient();
        $client->loginUser(
            UserBuilder::createAdmin()->build()
        );

        $file = new UploadedFile(
            path: $uploadFilePath,
            originalName: 'img_upload_temp.png',
            test: true,
        );

        $expectedPost = [
            'title' => 'Суккуленты 1',
            'shortTitle' => 'Суккуленты',
            'content' => 'Суккуленты - растения, приспособленные для хранения воды в своих листьях или стеблях',
            'categoryUuid' => '28912aa1-96ee-4631-8e34-14cd2f019e53',
            'metaKeyword' => 'суккуленты, уход за суккулентами',
            'metaDescription' => 'Суккуленты - идеальные растения для любителей минимального ухода',
        ];

        // action
        $client->request(
            method: 'POST',
            uri: self::ENDPOINT_URL,
            parameters: [
                'payload' => '{
                   "title":"' . $expectedPost['title'] . '",
                   "shortTitle":"' . $expectedPost['shortTitle'] . '",
                   "content":"' . $expectedPost['content'] . '",
                   "categoryUuid":"' . $expectedPost['categoryUuid'] . '",
                   "metaKeyword":"' . $expectedPost['metaKeyword'] . '",
                   "metaDescription":"' . $expectedPost['metaDescription'] . '"
                }',
            ],
            files: [
                'file' => $file,
            ]
        );

        // assert
        $response = $this->getDataFromJsonResponse($client->getResponse());

        $postCount = $this->postRepository->count([
            'shortTitle' => $expectedPost['shortTitle'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertEquals(
            'Данный сокращенный заголовок статьи уже существует.',
            $response['errors'][0]['detail']
        );
        self::assertEquals(1, $postCount);
    }
}
