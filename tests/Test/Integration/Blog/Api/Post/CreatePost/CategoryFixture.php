<?php

declare(strict_types=1);

namespace Test\Integration\Blog\Api\Post\CreatePost;

use Test\Integration\Common\Fixture\Blog\BaseCategoryFixture;

final class CategoryFixture extends BaseCategoryFixture
{
    public static function allItems(): array
    {
        return [
            [
                'id' => '28912aa1-96ee-4631-8e34-14cd2f019e53',
                'name' => 'Комнатные растения.',
                'description' => 'Растения, которые выращивают в помещении, обычно для декоративных целей.',
            ],
        ];
    }
}
