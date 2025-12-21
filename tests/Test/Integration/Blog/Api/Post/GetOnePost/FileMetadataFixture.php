<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\GetOnePost;

use Test\Integration\Common\Fixture\FileStorage\BaseFileMetadata;

final class FileMetadataFixture extends BaseFileMetadata
{
    public static function allItems(): array
    {
        $postItems = PostFixture::allItems();

        return [
            [
                'fileKey' => $postItems[0]['fileKey'],
                'name' => $postItems[0]['originalFileName'],
                'type' => 'image/jpeg',
                'extension' => 'jpg',
                'createdAt' => $postItems[0]['createdAt'],
            ],
        ];
    }
}
