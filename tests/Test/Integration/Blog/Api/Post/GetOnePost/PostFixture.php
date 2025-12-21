<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\GetOnePost;

use Test\Integration\Common\Fixture\Blog\BasePostFixture;

final class PostFixture extends BasePostFixture
{
    public static function allItems(): array
    {
        return [
            [
                'id' => '7f577086-fd10-4eb5-9db5-0e67a7c66743',
                'slug' => 'test-post-for-get-one',
                'title' => 'Тестовый пост',
                'shortTitle' => 'Тест пост',
                'content' => 'Это тестовый контент для проверки получения одного поста',
                'status' => 'published',
                'originalFileName' => 'test_image.jpg',
                'file' => 'test_image.jpg',
                'fileKey' => 'a587329c-d82a-461b-b9fc-96abc5992c8b',
                'imageType' => 'main',
                'createdAt' => '2025-07-15T09:00:00Z',
                'commentAvailable' => true,
                'metaKeyword' => 'тест, пост, получение',
                'metaDescription' => 'Тестовый пост для проверки получения одного поста',
            ],
        ];
    }
}
