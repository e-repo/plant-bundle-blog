<?php

declare(strict_types=1);

namespace Blog\Domain\Reader\Repository;

use Blog\Domain\Reader\Entity\Reader;

interface ReaderRepositoryInterface
{
    public function add(Reader $reader): void;

    public function findByEmail(string $email): ?Reader;

    public function findById(string $id): ?Reader;
}
