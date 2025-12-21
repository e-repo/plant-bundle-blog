<?php

declare(strict_types=1);

namespace Auth\UI\Http\V1\User\GetList;

use CoreKit\UI\Http\Response\ResponseInterface;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final readonly class Response implements ResponseInterface
{
    public function __construct(
        public string $id,
        public string $firstName,
        public ?string $lastName,
        public string $email,
        public string $role,
        public string $status,
        #[Context([
            DateTimeNormalizer::FORMAT_KEY => DATE_ATOM,
        ])]
        public DateTimeImmutable $createdAt,
    ) {}
}
