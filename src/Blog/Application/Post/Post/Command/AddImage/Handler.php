<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\AddImage;

use Blog\Application\Common\FileStorage\MimeTypeInterface;
use Blog\Application\Common\FileStorage\SystemFileType;
use Blog\Domain\Post\Entity\Dto\ImageDto;
use Blog\Domain\Post\Entity\ImageType;
use Blog\Domain\Post\Repository\PostRepositoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;
use CoreKit\Domain\Entity\Id;
use CoreKit\Domain\Service\FileStorage\FileUploadException;
use CoreKit\Domain\Service\FileStorage\StorageService;
use CoreKit\Domain\Service\FileStorage\Upload;
use DomainException;
use Psr\Log\LoggerInterface;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private StorageService $storageService,
        private MimeTypeInterface $mimeType,
        private PostRepositoryInterface $postRepository,
    ) {}

    public function __invoke(Command $command): void
    {
        $post = $this->postRepository->findByUuid($command->postId);

        if (null === $post) {
            unlink($command->file->getRealPath());

            throw new DomainException(
                sprintf('Пост по идентификатору \'%s\' не найден.', $command->postId)
            );
        }

        $upload = $this->makeUpload($command);

        try {
            $post->addImage(
                $this->makeImageDto(
                    upload: $upload,
                    imageType: $command->type
                )
            );

            $this->storageService
                ->addFileMetadataAndUpload(
                    $upload
                );
        } catch (FileUploadException $exception) {
            $this->logger->error('Ошибка загрузки изображения в хранилище', [
                'errorMessage' => $exception->getMessage(),
                'fileName' => $command->originalFileName,
            ]);

            throw new DomainException(
                'Не удалось загрузить изображение в хранилище. Попробуйте позднее или свяжитесь с администратором.'
            );
        } finally {
            unlink($upload->file->getRealPath());
        }
    }

    private function makeUpload(Command $command): Upload
    {
        $fileKey = Id::next();

        return new Upload(
            name: $command->originalFileName,
            key: $fileKey->value,
            systemFileType: SystemFileType::POST_IMAGE->value,
            mimeType: $this->mimeType
                ->guessMimeType(
                    $command->file->getRealPath()
                ),
            extension: $this->mimeType
                ->guessExtension(
                    $command->file->getRealPath()
                ),
            file: $command->file
        );
    }

    private function makeImageDto(
        Upload $upload,
        ImageType $imageType
    ): ImageDto {
        return new ImageDto(
            originalFileName: $upload->name,
            fileKey: new Id($upload->key),
            type: $imageType,
            file: $upload->file,
        );
    }
}
