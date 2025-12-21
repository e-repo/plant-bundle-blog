<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\AddImage;

use Blog\Domain\Post\Entity\ImageType;
use SplFileInfo;

final readonly class Command
{
    public function __construct(
        public string $postId,
        public SplFileInfo $file,
        public ImageType $type,
        public string $originalFileName,
        public string $systemFileType,
    ) {}
}
