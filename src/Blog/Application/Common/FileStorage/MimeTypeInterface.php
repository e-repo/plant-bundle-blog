<?php

declare(strict_types=1);

namespace Blog\Application\Common\FileStorage;

interface MimeTypeInterface
{
    public function guessMimeType(string $path): ?string;

    public function guessExtension(string $path): ?string;
}
