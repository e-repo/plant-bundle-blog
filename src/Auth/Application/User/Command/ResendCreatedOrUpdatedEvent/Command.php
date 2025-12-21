<?php

declare(strict_types=1);

namespace Auth\Application\User\Command\ResendCreatedOrUpdatedEvent;

final readonly class Command
{
    public function __construct(
        public string $eventName,
    ) {}
}
