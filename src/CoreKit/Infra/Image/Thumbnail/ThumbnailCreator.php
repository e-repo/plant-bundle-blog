<?php

declare(strict_types=1);

namespace CoreKit\Infra\Image\Thumbnail;

use CoreKit\Application\Service\Thumbnail\Option;
use CoreKit\Application\Service\Thumbnail\ThumbnailCreatorInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use SplFileInfo;

final class ThumbnailCreator implements ThumbnailCreatorInterface
{
    private SplFileInfo $file;

    private ImageInterface $image;

    public function __construct(
        private readonly ImageManager $imageManager,
    ) {}

    public function setFile(SplFileInfo $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setOption(Option $option): self
    {
        $this->imageManager
            ->driver()
            ->config()
            ->setOptions([
                'autoOrientation' => $option->autoOrientation,
                'decodeAnimation' => $option->decodeAnimation,
                'blendingColor' => $option->blendingColor,
            ]);

        return $this;
    }

    public function init(): self
    {
        $this->image = $this->imageManager->read($this->file);

        return $this;
    }

    public function resize(?int $width = null, ?int $height = null): self
    {
        $this->image->resize($width, $height);

        return $this;
    }

    public function scale(?int $width = null, ?int $height = null): self
    {
        $this->image->scale($width, $height);

        return $this;
    }

    public function save(?string $path = null): void
    {
        $this->image->save($path);
    }
}
