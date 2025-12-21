<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\Create\Command;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ImageCommand
{
    public function __construct(
        public UploadedFile $file,
        public string $extension,
        public string $originalFileName,
    ) {}
}
