<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity;

enum Status: string
{
    case ACTIVE = 'active';
    case WAIT = 'wait';
    case BLOCKED = 'blocked';

    public static function values(): array
    {
        return array_map(
            static function (self $case) {
                return $case->value;
            },
            self::cases()
        );
    }
}
