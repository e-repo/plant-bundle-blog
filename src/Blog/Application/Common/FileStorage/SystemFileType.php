<?php

declare(strict_types=1);

namespace Blog\Application\Common\FileStorage;

enum SystemFileType: string
{
    case POST_IMAGE = 'post_img';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
