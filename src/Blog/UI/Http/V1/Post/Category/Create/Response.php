<?php

declare(strict_types=1);

namespace Blog\UI\Http\V1\Post\Category\Create;

use CoreKit\UI\Http\Response\ResponseInterface;

final class Response implements ResponseInterface
{
    public function __construct(
        public string $status,
    ) {}
}
