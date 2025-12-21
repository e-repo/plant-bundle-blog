<?php

declare(strict_types=1);

namespace CoreKit\Domain\Service\FileStorage;

final readonly class Location
{
    public function __construct(
        public string $key,
        public string $type,
        public string $extension,
    ) {}
}
