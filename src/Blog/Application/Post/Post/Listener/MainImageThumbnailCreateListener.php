<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Listener;

use Blog\Application\Common\FileStorage\SystemFileType;
use Blog\Application\Post\Post\Command\AddImage\Command;
use Blog\Domain\Post\Entity\Event\MainImageAddedEvent;
use Blog\Domain\Post\Entity\ImageType;
use CoreKit\Application\Bus\CommandBusInterface;
use CoreKit\Application\Bus\EventListenerInterface;
use CoreKit\Application\Service\Thumbnail\ThumbnailCreatorFactoryInterface;
use CoreKit\Domain\Service\FileStorage\FileDownloadException;
use CoreKit\Domain\Service\FileStorage\FileNotFoundException;
use CoreKit\Domain\Service\FileStorage\Location;
use CoreKit\Domain\Service\FileStorage\StorageService;
use DomainException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;

readonly class MainImageThumbnailCreateListener implements EventListenerInterface
{
    public function __construct(
        private ThumbnailCreatorFactoryInterface $thumbnailCreatorFactory,
        private CommandBusInterface $commandBus,
        private StorageService $storageService,
        private LoggerInterface $logger,
        private array $thumbnails,
    ) {}

    public function __invoke(MainImageAddedEvent $event): void
    {
        if ($event->type !== ImageType::MAIN) {
            return;
        }

        $this->createThumbnails($event);
    }

    private function createThumbnails(MainImageAddedEvent $event): void
    {
        if (! isset($this->thumbnails['blog']['post']['height'])) {
            throw new RuntimeException('Ошибка определения высоты миниатюры поста.');
        }

        $file = $this->getThumbnailFile($event);

        $this->thumbnailCreatorFactory
            ->create($file)
            ->scale(
                height: $this->thumbnails['blog']['post']['height']
            )
            ->save();

        $this->commandBus->dispatch(
            new Command(
                postId: $event->postId->value,
                file: $file,
                type: ImageType::MAIN_THUMBNAIL_300,
                originalFileName: $event->originalFileName,
                systemFileType: SystemFileType::POST_IMAGE->value,
            )
        );
    }

    private function getThumbnailFile(MainImageAddedEvent $event): SplFileInfo
    {
        try {
            return $this->storageService
                ->download(
                    new Location(
                        key: $event->fileKey,
                        type: SystemFileType::POST_IMAGE->value,
                        extension: $event->extension,
                    )
                );
        } catch (FileDownloadException $exception) {
            $this->logger->error('Ошибка загрузки изображения в хранилище', [
                'errorMessage' => $exception->getMessage(),
                'fileName' => $event->originalFileName,
            ]);

            throw new DomainException(
                'Не удалось скачать изображение для формирования миниатюры. Попробуйте позднее или свяжитесь с администратором.'
            );
        } catch (FileNotFoundException $exception) {
            $this->logger->error('Ошибка загрузки изображения в хранилище', [
                'errorMessage' => $exception->getMessage(),
                'fileName' => $event->originalFileName,
            ]);

            throw new DomainException(
                'Изображение для формирования миниатюры отсутствует. Попробуйте позднее или свяжитесь с администратором.'
            );
        }
    }
}
