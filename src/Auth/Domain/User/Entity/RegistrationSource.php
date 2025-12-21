<?php

declare(strict_types=1);

namespace Auth\Domain\User\Entity;

enum RegistrationSource: string
{
    case BLOG = 'blog';
    case ADMIN_PANEL = 'admin_panel';
    case SYSTEM = 'system';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
