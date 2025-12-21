<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Repository;

use Blog\Domain\Post\Entity\Post;

interface PostRepositoryInterface
{
    public function add(Post $post): void;

    public function findByUuid(string $uuid): ?Post;

    public function findBySlug(string $slug): ?Post;

    public function findByTitle(string $title): ?Post;

    public function findByShortTitle(string $shortTitle): ?Post;
}
