<?php

declare(strict_types=1);

namespace Test\Unit\Blog\Post;

use Blog\Application\Post\Post\Listener\MainImageThumbnailCreateListener;
use Blog\Domain\Post\Entity\Category;
use Blog\Domain\Post\Entity\Dto\ContentDto;
use Blog\Domain\Post\Entity\Dto\ImageDto;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\Domain\Post\Entity\Event\MainImageAddedEvent;
use Blog\Domain\Post\Entity\Event\PostCreatedEvent;
use Blog\Domain\Post\Entity\ImageType;
use Blog\Domain\Post\Entity\Post;
use Blog\Domain\Post\Entity\Specification\Post\SpecificationAggregator;
use Blog\Domain\Post\Entity\Specification\Post\UniqueShortTitle;
use Blog\Domain\Post\Entity\Specification\Post\UniqueTitle;
use Blog\Domain\Post\Entity\Status;
use CoreKit\Domain\Entity\Id;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AddPostImageTest extends KernelTestCase
{
    public Category $categoryMock;

    public SpecificationAggregator $specificationAggregator;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryMock = $this->createMock(Category::class);
        $this->categoryMock
            ->method('getId')
            ->willReturn(Id::next());

        $this->categoryMock
            ->method('getName')
            ->willReturn('Комнатные растения.');

        $uniqueShortTitleSpecificationStub = $this->createStub(UniqueShortTitle::class);
        $uniqueShortTitleSpecificationStub
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $uniqueTitleSpecificationStub = $this->createStub(UniqueTitle::class);
        $uniqueTitleSpecificationStub
            ->method('isSatisfiedBy')
            ->willReturn(true);

        self::getContainer()->set(UniqueShortTitle::class, $uniqueShortTitleSpecificationStub);
        self::getContainer()->set(UniqueTitle::class, $uniqueTitleSpecificationStub);

        $this->specificationAggregator = self::getContainer()->get(SpecificationAggregator::class);

        $mainImageAddedListenerMock = $this->createMock(MainImageThumbnailCreateListener::class);
        self::getContainer()->set(MainImageThumbnailCreateListener::class, $mainImageAddedListenerMock);
    }

    public function testSuccess(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img.png';

        $postId = Uuid::uuid4()->toString();
        $fileKey = Uuid::uuid4()->toString();

        $postDto = new PostDto(
            slug: 'bonsay',
            title: 'Бонсай',
            shortTitle: 'Бонсай',
            content: new ContentDto('Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре'),
            status: Status::DRAFT,
            image: new ImageDto(
                originalFileName: 'img.png',
                fileKey: new Id($fileKey),
                type: ImageType::MAIN,
                file: new SplFileInfo($uploadFilePath)
            ),
            createdAt: new DateTimeImmutable(),
            id: $postId,
        );

        // action
        $post = new Post(
            postDto: $postDto, // добавляет изображение в момент создания
            category: $this->categoryMock,
            specificationAggregator: $this->specificationAggregator,
        );

        // assert
        self::assertCount(1, $post->getImages());
        self::assertCount(2, $post->getRecordedEvents());
        self::assertEquals($fileKey, $post->getImages()->first()->getFileKey()->value);
        self::assertEquals($postId, $post->getId()->value);
        self::assertEquals($postDto->slug, $post->getSlug());
        self::assertEquals($postDto->title, $post->getTitle());
        self::assertEquals($postDto->shortTitle, $post->getShortTitle());
        self::assertEquals($postDto->content->text, $post->getContent());
        self::assertEquals($postDto->status, $post->getStatus());

        $postCreatedEvents = array_filter(
            $post->getRecordedEvents(),
            static fn ($event) => $event instanceof PostCreatedEvent
        );
        /** @var PostCreatedEvent $postCreatedEvent */
        $postCreatedEvent = reset($postCreatedEvents);

        self::assertEquals($postId, $postCreatedEvent->id);
        self::assertEquals($postDto->slug, $postCreatedEvent->slug);
        self::assertEquals($postDto->title, $postCreatedEvent->title);
        self::assertEquals($postDto->status, $postCreatedEvent->status);
        self::assertEquals('Комнатные растения.', $postCreatedEvent->categoryName);

        $mainImageAddedEvents = array_filter(
            $post->getRecordedEvents(),
            static fn ($event) => $event instanceof MainImageAddedEvent
        );
        /** @var MainImageAddedEvent $mainImageAddedEvent */
        $mainImageAddedEvent = reset($mainImageAddedEvents);

        self::assertEquals($postId, $mainImageAddedEvent->postId->value);
        self::assertEquals(ImageType::MAIN, $mainImageAddedEvent->type);
        self::assertEquals($fileKey, $mainImageAddedEvent->fileKey);
        self::assertEquals('png', $mainImageAddedEvent->extension);
        self::assertEquals('img.png', $mainImageAddedEvent->originalFileName);
    }

    public function testFailedByImageTypeAlreadyExists(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img.png';

        $postId = Uuid::uuid4()->toString();
        $fileKey = Uuid::uuid4()->toString();

        $postDto = new PostDto(
            slug: 'bonsay',
            title: 'Бонсай',
            shortTitle: 'Бонсай',
            content: new ContentDto('Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре'),
            status: Status::DRAFT,
            image: new ImageDto(
                originalFileName: 'img.png',
                fileKey: new Id($fileKey),
                type: ImageType::MAIN,
                file: new SplFileInfo($uploadFilePath)
            ),
            createdAt: new DateTimeImmutable(),
            id: $postId,
        );

        // action
        $post = new Post(
            postDto: $postDto,
            category: $this->categoryMock,
            specificationAggregator: $this->specificationAggregator,
        );

        $exceptionMessage = null;

        try {
            $post->addImage(
                new ImageDto(
                    originalFileName: 'img.png',
                    fileKey: new Id($fileKey),
                    type: ImageType::MAIN,
                    file: new SplFileInfo($uploadFilePath)
                ),
            );
        } catch (DomainException $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        // assert
        self::assertNotNull($exceptionMessage);
        self::assertMatchesRegularExpression(
            "/Данное изображение уже было добавлено, fileKey - '.+?'\./",
            $exceptionMessage
        );
        self::assertCount(1, $post->getImages());
    }

    public function testFailedByImageAlreadyExists(): void
    {
        // arrange
        $uploadFilePath = __DIR__ . '/Data/img.png';

        $postId = Uuid::uuid4()->toString();
        $fileKey = Uuid::uuid4()->toString();

        $postDto = new PostDto(
            slug: 'bonsay',
            title: 'Бонсай',
            shortTitle: 'Бонсай',
            content: new ContentDto('Бонсай - искусство выращивания точной копии настоящего дерева в миниатюре'),
            status: Status::DRAFT,
            image: new ImageDto(
                originalFileName: 'img.png',
                fileKey: new Id($fileKey),
                type: ImageType::MAIN,
                file: new SplFileInfo($uploadFilePath)
            ),
            createdAt: new DateTimeImmutable(),
            id: $postId,
        );

        // action
        $post = new Post(
            postDto: $postDto,
            category: $this->categoryMock,
            specificationAggregator: $this->specificationAggregator,
        );

        $exceptionMessage = null;

        try {
            $post->addImage(
                new ImageDto(
                    originalFileName: 'img.png',
                    fileKey: new Id($fileKey),
                    type: ImageType::MAIN_THUMBNAIL_300,
                    file: new SplFileInfo($uploadFilePath)
                )
            );
        } catch (DomainException $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        // assert
        self::assertNotNull($exceptionMessage);
        self::assertMatchesRegularExpression(
            "/Данное изображение уже было добавлено, fileKey - .+/",
            $exceptionMessage
        );
        self::assertCount(1, $post->getImages());
    }
}
