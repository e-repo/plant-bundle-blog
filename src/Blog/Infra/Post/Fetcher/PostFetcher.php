<?php

declare(strict_types=1);

namespace Blog\Infra\Post\Fetcher;

use Blog\Domain\Post\Entity\Dto\ContentDto;
use Blog\Domain\Post\Entity\Dto\ImageDto;
use Blog\Domain\Post\Entity\Dto\MetadataDto;
use Blog\Domain\Post\Entity\Dto\PostDto;
use Blog\Domain\Post\Entity\ImageType;
use Blog\Domain\Post\Entity\Status;
use Blog\Domain\Post\Fetcher\PostFetcherInterface;
use Carbon\CarbonImmutable;
use CoreKit\Domain\Entity\Id;
use CoreKit\Infra\BaseFetcher;
use Doctrine\DBAL\Exception;

final readonly class PostFetcher extends BaseFetcher implements PostFetcherInterface
{
    private const TABLE_NAME = 'blog.post';

    /**
     * @throws Exception
     */
    public function findByUuid(string $uuid): ?PostDto
    {
        $qb = $this->createDBALQueryBuilder();

        $post = $qb
            ->select(
                'p.id as id',
                'p.slug as slug',
                'p.title as title',
                'p.short_title as short_title',
                'p.content as content',
                'p.status as status',
                'p.comment_available as comment_available',
                'p.meta_keyword as meta_keyword',
                'p.meta_description as meta_description',
                'p.created_at as created_at',
                'c.name as category_name',
                'pi.file_key as image_file_key',
                'pi.type as image_type',
                'fm.extension as image_extension',
                'fm.name as image_original_name',
            )
            ->from(self::TABLE_NAME, 'p')
            ->leftJoin(
                'p',
                'blog.category',
                'c',
                'p.category_id = c.id'
            )
            ->leftJoin(
                'p',
                'blog.post_image',
                'pi',
                'p.id = pi.post_id and pi.type = :imageType',
            )
            ->leftJoin(
                'pi',
                'file_metadata',
                'fm',
                'fm.key = pi.file_key'
            )
            ->where('p.id = :uuid')
            ->setParameter('uuid', $uuid)
            ->setParameter('imageType', ImageType::MAIN->value)
            ->fetchAssociative();

        return $post ? $this->toPostDto($post) : null;
    }

    private function toPostDto(array $post): PostDto
    {
        return new PostDto(
            slug: $post['slug'],
            title: $post['title'],
            shortTitle: $post['short_title'],
            content: new ContentDto($post['content']),
            status: Status::from($post['status']),
            image: new ImageDto(
                originalFileName: $post['image_original_name'],
                fileKey: new Id($post['image_file_key']),
                type: ImageType::from($post['image_type']),
                extension: $post['image_extension'],
            ),
            createdAt: CarbonImmutable::parse($post['created_at']),
            id: $post['id'],
            categoryName: $post['category_name'] ?? null,
            commentAvailable: (bool) $post['comment_available'],
            meta: new MetadataDto(
                keyword: $post['meta_keyword'] ?? null,
                description: $post['meta_description'] ?? null,
            ),
        );
    }
}
