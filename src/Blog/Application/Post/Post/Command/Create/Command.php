<?php

declare(strict_types=1);

namespace Blog\Application\Post\Post\Command\Create;

use Blog\Application\Post\Post\Command\Create\Command\ImageCommand;
use Blog\Application\Post\Post\Command\Create\Command\MetaCommand;

final readonly class Command
{
    public function __construct(
        public ImageCommand $image,
        public string $title,
        public string $shortTitle,
        public string $content,
        public string $categoryId,
        public ?MetaCommand $meta = null
    ) {}
}
