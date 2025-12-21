<?php

declare(strict_types=1);

namespace Blog\Domain\Post\Entity\Specification\Post;

use Blog\Domain\Post\Entity\Post;
use Blog\Domain\Post\Repository\PostRepositoryInterface;
use CoreKit\Domain\Entity\SpecificationInterface;

readonly class UniqueTitle implements SpecificationInterface
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @param Post $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $post = $this->postRepository->findByTitle(
            $candidate->getTitle(),
        );

        return null === $post;
    }
}
