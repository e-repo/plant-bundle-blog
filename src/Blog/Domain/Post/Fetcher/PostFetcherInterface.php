<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Fetcher;

use Blog\Domain\Post\Entity\Dto\PostDto;

interface PostFetcherInterface
{
    public function findByUuid(string $uuid): ?PostDto;
}
