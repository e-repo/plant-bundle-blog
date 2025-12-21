<?php

declare(strict_types=1);

namespace Auth\Application\User\Service\ResendEvent;

interface ResendEventFactoryInterface
{
    public function create(string $eventName): DispatcherInterface;
}
