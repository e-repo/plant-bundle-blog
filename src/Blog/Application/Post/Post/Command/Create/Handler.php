<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\Create;

use Blog\Application\Common\FileStorage\MimeTypeInterface;
use Blog\Application\Common\FileStorage\SystemFileType;
use Blog\Domain\Post\Entity\Dto\ContentDto;
use Blog\Domain\Post\Entity\Dto\ImageDto;
use Blog\Domain\Post\Entity\Dto\MetadataDto;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\Domain\Post\Entity\ImageType;
use Blog\Domain\Post\Entity\Post;
use Blog\Domain\Post\Entity\Specification\Post\SpecificationAggregator;
use Blog\Domain\Post\Entity\Status;
use Blog\Domain\Post\Repository\CategoryRepositoryInterface;
use Blog\Domain\Post\Repository\PostRepositoryInterface;
use CoreKit\Application\Bus\CommandHandlerInterface;
use CoreKit\Domain\Entity\Id;
use CoreKit\Domain\Service\FileStorage\FileUploadException;
use CoreKit\Domain\Service\FileStorage\StorageService;
use CoreKit\Domain\Service\FileStorage\Upload;
use DateTimeImmutable;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final readonly class Handler implements CommandHandlerInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private SpecificationAggregator $specificationAggregator,
        private StorageService $storageService,
        private PostRepositoryInterface $postRepository,
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
        private MimeTypeInterface $mimeType,
    ) {}

    public function __invoke(Command $command): void
    {
        $category = $this->categoryRepository
            ->findById(
                $command->categoryId
            );

        if (null === $category) {
            unlink($command->image->file->getRealPath());

            throw new DomainException('Категория не найдена.');
        }

        try {
            $fileKey = Id::next();

            $post = new Post(
                postDto: $this->makePost($command, $fileKey),
                category: $category,
                specificationAggregator: $this->specificationAggregator,
            );

            $this->postRepository->add($post);

            $this->sendImageToStorage($command->image, $fileKey);
        } finally {
            unlink($command->image->file->getRealPath());
        }
    }

    private function makePost(Command $command, Id $fileKey): PostDto
    {
        $slug = $this->slugger
            ->slug(
                $command->shortTitle
            )
            ->toString();

        $meta = null;

        if (null !== $command->meta) {
            $meta = $this->makeMeta(
                $command->meta
            );
        }

        return new PostDto(
            slug: strtolower($slug),
            title: $command->title,
            shortTitle: $command->shortTitle,
            content: new ContentDto($command->content),
            status: Status::DRAFT,
            image: $this->makeImage(
                file: $command->image->file,
                fileKey: $fileKey,
                originalFileName: $command->image->originalFileName,
            ),
            createdAt: new DateTimeImmutable(),
            meta: $meta,
        );
    }

    private function makeImage(
        UploadedFile $file,
        Id $fileKey,
        string $originalFileName,
    ): ImageDto {
        return new ImageDto(
            originalFileName: $originalFileName,
            fileKey: $fileKey,
            type: ImageType::MAIN,
            file: $file->getFileInfo(),
            createdAt: new DateTimeImmutable(),
        );
    }

    private function makeMeta(Command\MetaCommand $meta): MetadataDto
    {
        return new MetadataDto(
            keyword: $meta->keyword,
            description: $meta->description,
        );
    }

    private function sendImageToStorage(
        Command\ImageCommand $image,
        Id $fileKey
    ): void {
        $upload = new Upload(
            name: $image->originalFileName,
            key: $fileKey->value,
            systemFileType: SystemFileType::POST_IMAGE->value,
            mimeType: $this->mimeType
                ->guessMimeType(
                    $image->file->getRealPath()
                ),
            extension: $image->extension,
            file: $image->file
        );

        try {
            $this->storageService->addFileMetadataAndUpload($upload);
        } catch (FileUploadException $exception) {
            $this->logger->error('Ошибка загрузки изображения в хранилище', [
                'errorMessage' => $exception->getMessage(),
                'fileName' => $image->originalFileName,
            ]);

            throw new DomainException(
                'Не удалось загрузить изображение в хранилище. Попробуйте позднее или свяжитесь с администратором.'
            );
        }
    }
}
