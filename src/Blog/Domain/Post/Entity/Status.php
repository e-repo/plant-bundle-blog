<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity;

enum Status: string
{
    case PUBLISHED = 'published';

    case DRAFT = 'draft';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
