<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Reader\Create;

use Test\Integration\Common\Fixture\Blog\BaseReaderFixture;

final class ReaderFixture extends BaseReaderFixture
{
    public static function allItems(): array
    {
        return [
            [
                'id' => 'f472d1a5-ba78-4039-94e3-ae0161256eaf',
                'firstName' => 'Федор',
                'lastName' => 'Достоевский',
                'email' => 'test_1@test.ru',
            ],
        ];
    }
}
