<?php

declare(strict_types=1);

namespace CoreKit\Infra\Image\Thumbnail;

use CoreKit\Application\Service\Thumbnail\Option;
use CoreKit\Application\Service\Thumbnail\ThumbnailCreatorFactoryInterface;
use CoreKit\Application\Service\Thumbnail\ThumbnailCreatorInterface;
use SplFileInfo;

final class ThumbnailCreatorFactory implements ThumbnailCreatorFactoryInterface
{
    public function __construct(
        protected ThumbnailCreatorInterface $thumbnailCreator
    ) {}

    public function create(SplFileInfo $file, ?Option $option = null): ThumbnailCreatorInterface
    {
        if (null !== $option) {
            $this->thumbnailCreator->setOption($option);
        }

        return $this->thumbnailCreator
            ->setFile($file)
            ->init();
    }
}
