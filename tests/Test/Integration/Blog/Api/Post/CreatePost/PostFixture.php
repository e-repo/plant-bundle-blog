<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\CreatePost;

use Test\Integration\Common\Fixture\Blog\BasePostFixture;

final class PostFixture extends BasePostFixture
{
    public static function allItems(): array
    {
        return [
            [
                'id' => 'c10802fa-9c47-4837-b522-c3cd1a6e0bf0',
                'slug' => 'sukkulenty',
                'title' => 'Суккуленты',
                'shortTitle' => 'Суккуленты',
                'content' => 'Суккуленты - растения, приспособленные для хранения воды в своих листьях или стеблях',
                'status' => 'draft',
                'originalFileName' => 'img_temp.png',
                'file' => 'img_temp.png',
                'fileKey' => '4587329c-d82a-461b-b9fc-96abc5992c8b',
                'imageType' => 'content',
                'commentAvailable' => false,
                'metaKeyword' => 'суккуленты, уход за суккулентами',
                'metaDescription' => 'Суккуленты - идеальные растения для любителей минимального ухода',
            ],
        ];
    }
}
