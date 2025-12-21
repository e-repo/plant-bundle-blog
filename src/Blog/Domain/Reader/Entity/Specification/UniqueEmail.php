<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Entity\Specification;

use Blog\Domain\Reader\Entity\Reader;
use Blog\Domain\Reader\Repository\ReaderRepositoryInterface;
use CoreKit\Domain\Entity\SpecificationInterface;

final readonly class UniqueEmail implements SpecificationInterface
{
    public function __construct(
        private ReaderRepositoryInterface $readerRepository
    ) {}

    /**
     * @param Reader $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        $user = $this->readerRepository->findByEmail(
            $candidate->getEmail()->value
        );

        return null === $user;
    }
}
