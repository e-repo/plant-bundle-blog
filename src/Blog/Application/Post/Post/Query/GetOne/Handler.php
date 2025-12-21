<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Query\GetOne;

use Blog\Application\Common\FileStorage\SystemFileType;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\Domain\Post\Fetcher\PostFetcherInterface;
use CoreKit\Application\Bus\QueryHandlerInterface;
use CoreKit\Domain\Service\FileStorage\Location;
use CoreKit\Domain\Service\FileStorage\StorageService;
use DomainException;

final readonly class Handler implements QueryHandlerInterface
{
    public function __construct(
        private PostFetcherInterface $postFetcher,
        private StorageService $storageService
    ) {}

    public function __invoke(Query $query): PostDto
    {
        $post = $this->postFetcher->findByUuid($query->id);

        if (null === $post) {
            throw new DomainException('Пост не найден.');
        }

        $post->image->setUrl($this->getPostImageUrl($post));
        return $post;
    }

    private function getPostImageUrl(PostDto $postDto): string
    {
        $mainImage = $postDto->image;

        return $this->storageService
            ->getPublicUrl(
                new Location(
                    key: $mainImage->fileKey->value,
                    type: SystemFileType::POST_IMAGE->value,
                    extension: $mainImage->extension,
                )
            );
    }
}
