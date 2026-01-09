<?php

declare(strict_types=1);

namespace CoreKit\Domain\Service\FileStorage;

use CoreKit\Domain\Entity\FileMetadata;
use CoreKit\Domain\Repository\FileMetadataRepositoryInterface;
use DateTimeImmutable;
use DateTimeInterface;
use League\Flysystem\UnableToReadFile;
use SplFileInfo;
use Throwable;

final readonly class StorageService
{
    public function __construct(
        private StorageClientInterface $storage,
        private FileMetadataRepositoryInterface $fileMetadataRepository,
    ) {}

    /**
     * @throws FileUploadException
     */
    public function upload(Upload $upload): void
    {
        $location = $this->makeLocationLine(
            fileType: $upload->systemFileType,
            extension: $upload->extension,
            key: $upload->key
        );

        $file = fopen($upload->file->getRealPath(), 'rb');

        try {
            $this->storage->upload(
                resource: $file,
                location: $location
            );
        } catch (Throwable $exception) {
            throw new FileUploadException(
                fileName: $upload->name,
                previous: $exception
            );
        }
    }

    /**
     * @throws FileUploadException
     */
    public function addFileMetadataAndUpload(Upload $upload): void
    {
        $this->addFileMetadata($upload);

        $this->upload($upload);
    }

    /**
     * @throws FileDownloadException
     * @throws FileNotFoundException
     */
    public function download(Location $location): SplFileInfo
    {
        $locationLine = $this->makeLocationLine(
            fileType: $location->type,
            extension: $location->extension,
            key: $location->key
        );

        try {
            $stream = $this->storage->download($locationLine);
        } catch (UnableToReadFile $exception) {
            throw new FileNotFoundException(
                fileKey: $location->key,
                previous: $exception,
            );
        } catch (Throwable $exception) {
            throw new FileDownloadException(
                fileKey: $location->key,
                previous: $exception,
            );
        }

        $file = tempnam(
            directory: sys_get_temp_dir(),
            prefix: sprintf('%s_', $location->type)
        );

        $fileWithExtension = $file . '.' . $location->extension;
        rename($file, $fileWithExtension);

        file_put_contents($fileWithExtension, $stream);

        return new SplFileInfo($fileWithExtension);
    }

    /**
     * @throws FileNotFoundException
     * @throws FileDeleteException
     */
    public function delete(Location $location): void
    {
        $locationLine = $this->makeLocationLine(
            fileType: $location->type,
            extension: $location->extension,
            key: $location->key
        );

        try {
            $this->storage->delete($locationLine);
        } catch (UnableToReadFile $exception) {
            throw new FileNotFoundException(
                fileKey: $location->key,
                previous: $exception,
            );
        } catch (Throwable $exception) {
            throw new FileDeleteException(
                fileKey: $location->key,
                previous: $exception,
            );
        }
    }

    public function getPublicUrl(Location $location): string
    {
        $locationLine = $this->makeLocationLine(
            fileType: $location->type,
            extension: $location->extension,
            key: $location->key
        );

        return $this->storage->publicUrl($locationLine);
    }

    public function getTemporaryUrl(Location $location, DateTimeInterface $expiresAt): string
    {
        $locationLine = $this->makeLocationLine(
            fileType: $location->type,
            extension: $location->extension,
            key: $location->key
        );

        return $this->storage->temporaryUrl($locationLine, $expiresAt);
    }

    private function makeLocationLine(
        string $fileType,
        string $extension,
        string $key
    ): string {
        return sprintf('%s/%s/%s', $fileType, $extension, $key);
    }

    private function addFileMetadata(Upload $upload): void
    {
        $fileMetadata = new FileMetadata(
            key: $upload->key,
            name: $upload->name,
            type: $upload->mimeType,
            extension: $upload->extension,
            createdAt: new DateTimeImmutable(),
        );

        $this->fileMetadataRepository->add($fileMetadata);
    }
}
