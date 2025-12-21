<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Category\GetList;

use CoreKit\UI\Http\Response\ResponseInterface;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class Response implements ResponseInterface
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        #[Context([
            DateTimeNormalizer::FORMAT_KEY => DATE_ATOM,
        ])]
        public DateTimeImmutable $createdAt,
    ) {}
}
